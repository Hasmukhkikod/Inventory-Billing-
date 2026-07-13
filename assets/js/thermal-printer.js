/**
 * IIMS v2.0 - Direct Thermal Receipt Printing (USB / Bluetooth / WiFi-LAN)
 *
 * Talks straight to an ESC/POS receipt printer, bypassing the OS/browser print
 * dialog entirely. The receipt is rasterized to an image (via html2canvas) and
 * sent as a raw ESC/POS raster bit-image, so output is pixel-identical to the
 * on-screen receipt across all three transports - no font/currency-symbol
 * compatibility issues with the printer's built-in character set.
 *
 * See docs/thermal-printing.md for setup, requirements and troubleshooting
 * for each transport.
 */
window.GrovixoThermalPrinter = (function () {
    const PREF_KEY = 'grovixo_thermal_printer_pref';

    // Very common BLE "transparent UART" service used by a large share of
    // generic Chinese ESC/POS thermal printer BLE modules (ISSC/Microchip
    // profile). Printers using Classic Bluetooth instead of BLE cannot be
    // reached by a browser at all - that's a platform limitation, not
    // something fixable in JS.
    const BLE_SERVICE_UUID = '49535343-fe7d-4ae5-8fa9-9fafd205e455';
    const BLE_WRITE_CHAR_UUID = '49535343-8841-43f4-a8d4-ecbe34729bb3';
    const BLE_CHUNK_SIZE = 180;
    const BLE_CHUNK_DELAY_MS = 20;

    // ==================== Shared: rasterize + ESC/POS encode ====================

    function canvasToEscPosRaster(canvas) {
        const ctx = canvas.getContext('2d');
        const { width, height } = canvas;
        const img = ctx.getImageData(0, 0, width, height).data;
        const bytesPerRow = Math.ceil(width / 8);
        const raster = new Uint8Array(bytesPerRow * height);

        for (let y = 0; y < height; y++) {
            for (let x = 0; x < width; x++) {
                const idx = (y * width + x) * 4;
                const r = img[idx], g = img[idx + 1], b = img[idx + 2], a = img[idx + 3];
                const luminance = r * 0.299 + g * 0.587 + b * 0.114;
                const isDark = a > 64 && luminance < 180;
                if (isDark) {
                    raster[y * bytesPerRow + (x >> 3)] |= 0x80 >> (x & 7);
                }
            }
        }
        return { bytesPerRow, height, raster };
    }

    function buildEscPosRasterCommand({ bytesPerRow, height, raster }) {
        const xL = bytesPerRow & 0xFF, xH = (bytesPerRow >> 8) & 0xFF;
        const yL = height & 0xFF, yH = (height >> 8) & 0xFF;
        // GS v 0 - print raster bit image, mode 0 (normal).
        const header = new Uint8Array([0x1D, 0x76, 0x30, 0x00, xL, xH, yL, yH]);
        const out = new Uint8Array(header.length + raster.length);
        out.set(header, 0);
        out.set(raster, header.length);
        return out;
    }

    async function rasterizeElement(element, widthDots) {
        if (typeof html2canvas === 'undefined') {
            throw new Error('html2canvas is not loaded on this page.');
        }
        const scale = widthDots / element.offsetWidth;
        const canvas = await html2canvas(element, { scale, useCORS: true, backgroundColor: '#ffffff' });
        if (canvas.width === widthDots) return canvas;

        // html2canvas' scale factor can land a pixel or two off - snap to the
        // printer's exact dot width so the raster command's row byte-count is correct.
        const exact = document.createElement('canvas');
        exact.width = widthDots;
        exact.height = Math.round(canvas.height * (widthDots / canvas.width));
        exact.getContext('2d').drawImage(canvas, 0, 0, exact.width, exact.height);
        return exact;
    }

    /** @returns {Promise<Uint8Array>} full ESC/POS payload: init + raster image + feed/cut. */
    async function buildPrintPayload(element, widthDots) {
        const canvas = await rasterizeElement(element, widthDots);
        const rasterCmd = buildEscPosRasterCommand(canvasToEscPosRaster(canvas));

        const ESC_INIT = new Uint8Array([0x1B, 0x40]);
        const FEED_AND_CUT = new Uint8Array([0x0A, 0x0A, 0x0A, 0x0A, 0x1D, 0x56, 0x00]);

        const payload = new Uint8Array(ESC_INIT.length + rasterCmd.length + FEED_AND_CUT.length);
        payload.set(ESC_INIT, 0);
        payload.set(rasterCmd, ESC_INIT.length);
        payload.set(FEED_AND_CUT, ESC_INIT.length + rasterCmd.length);
        return payload;
    }

    function sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    // ==================== USB ====================

    function isUsbSupported() {
        return typeof navigator !== 'undefined' && !!navigator.usb;
    }

    async function usbPickDevice() {
        // No vendor/product filter - there's no single USB ID standard across
        // ESC/POS thermal printer brands, so let the user pick from the OS chooser.
        return navigator.usb.requestDevice({ filters: [] });
    }

    async function usbGetPairedDevice() {
        const devices = await navigator.usb.getDevices();
        return devices[0] || null;
    }

    async function usbForgetPairedDevice() {
        const device = await usbGetPairedDevice();
        if (device) await device.forget?.();
    }

    async function usbOpenAndClaim(device) {
        await device.open();
        if (device.configuration === null) {
            await device.selectConfiguration(1);
        }
        let targetInterface = null;
        let outEndpoint = null;
        for (const iface of device.configuration.interfaces) {
            for (const alt of iface.alternates) {
                const ep = alt.endpoints.find(e => e.direction === 'out');
                if (ep) {
                    targetInterface = iface;
                    outEndpoint = ep;
                    break;
                }
            }
            if (targetInterface) break;
        }
        if (!targetInterface) {
            throw new Error('No writable USB endpoint found on this device - it may not be a raw ESC/POS printer, or it is claimed by an OS print driver.');
        }
        await device.claimInterface(targetInterface.interfaceNumber);
        return { device, interfaceNumber: targetInterface.interfaceNumber, endpointNumber: outEndpoint.endpointNumber };
    }

    /** @param {{widthDots?: number}} options - 576 dots = 80mm, 384 = 58mm (both @ 203dpi). */
    async function printViaUsb(element, options = {}) {
        if (!isUsbSupported()) {
            throw new Error('This browser does not support direct USB printing. Use Chrome or Edge.');
        }
        const widthDots = options.widthDots || 576;
        const payload = await buildPrintPayload(element, widthDots);

        let device = await usbGetPairedDevice();
        if (!device) device = await usbPickDevice();
        if (!device) throw new Error('No printer was selected.');

        const conn = await usbOpenAndClaim(device);
        try {
            // Chunked writes - many budget USB thermal printers choke on one huge transfer.
            const CHUNK = 4096;
            for (let offset = 0; offset < payload.length; offset += CHUNK) {
                await conn.device.transferOut(conn.endpointNumber, payload.slice(offset, offset + CHUNK));
            }
        } finally {
            await conn.device.releaseInterface(conn.interfaceNumber).catch(() => {});
            await conn.device.close().catch(() => {});
        }
    }

    // ==================== Bluetooth (BLE only) ====================

    function isBluetoothSupported() {
        return typeof navigator !== 'undefined' && !!navigator.bluetooth;
    }

    async function bluetoothGetPairedDevice() {
        // Requires Chrome's persistent Bluetooth permissions (Chrome 105+); on
        // older versions this returns an empty list and pairing falls back to
        // the picker every time.
        if (!navigator.bluetooth.getDevices) return null;
        const devices = await navigator.bluetooth.getDevices();
        return devices[0] || null;
    }

    async function bluetoothPickDevice() {
        return navigator.bluetooth.requestDevice({
            filters: [{ services: [BLE_SERVICE_UUID] }]
        });
    }

    async function bluetoothForgetPairedDevice() {
        const device = await bluetoothGetPairedDevice();
        if (device) await device.forget?.();
    }

    /** Pair-only helpers (no print) - used by Settings to confirm pairing works. */
    async function pairUsb() {
        if (!isUsbSupported()) throw new Error('This browser does not support USB pairing. Use Chrome or Edge.');
        const device = await usbPickDevice();
        if (!device) throw new Error('No device was selected.');
        return device.productName || device.manufacturerName || 'USB device';
    }

    async function pairBluetooth() {
        if (!isBluetoothSupported()) throw new Error('This browser does not support Bluetooth pairing. Use Chrome or Edge.');
        const device = await bluetoothPickDevice();
        if (!device) throw new Error('No device was selected.');
        return device.name || 'Bluetooth device';
    }

    async function printViaBluetooth(element, options = {}) {
        if (!isBluetoothSupported()) {
            throw new Error('This browser does not support Bluetooth printing. Use Chrome or Edge, and make sure Bluetooth is on.');
        }
        const widthDots = options.widthDots || 576;
        const payload = await buildPrintPayload(element, widthDots);

        let device = await bluetoothGetPairedDevice();
        if (!device) device = await bluetoothPickDevice();
        if (!device) throw new Error('No printer was selected.');

        let server;
        try {
            server = await device.gatt.connect();
        } catch (e) {
            throw new Error('Could not connect over Bluetooth. Make sure the printer is powered on and in range. (' + e.message + ')');
        }

        try {
            let service;
            try {
                service = await server.getPrimaryService(BLE_SERVICE_UUID);
            } catch (e) {
                throw new Error('This printer does not expose the expected Bluetooth print service - it likely uses Classic Bluetooth (SPP), which browsers cannot access at all. Try USB instead.');
            }
            const characteristic = await service.getCharacteristic(BLE_WRITE_CHAR_UUID);

            // BLE writes need small chunks and pacing - most cheap printer
            // modules drop bytes if flooded faster than they can buffer/print.
            for (let offset = 0; offset < payload.length; offset += BLE_CHUNK_SIZE) {
                const chunk = payload.slice(offset, offset + BLE_CHUNK_SIZE);
                if (characteristic.writeValueWithoutResponse) {
                    await characteristic.writeValueWithoutResponse(chunk);
                } else {
                    await characteristic.writeValue(chunk);
                }
                await sleep(BLE_CHUNK_DELAY_MS);
            }
        } finally {
            device.gatt.disconnect();
        }
    }

    // ==================== WiFi / LAN (via server-side relay) ====================
    // Browsers have no raw TCP socket API, so a LAN printer (raw port 9100,
    // the universal "AppSocket"/JetDirect printing protocol most network
    // printers speak) has to be reached through the backend. This only works
    // if the web server itself can reach the printer's network - true for
    // on-premise/local installs, not for a cloud-hosted app printing to a
    // printer behind the shop's own router.

    function bytesToBase64(bytes) {
        let binary = '';
        for (let i = 0; i < bytes.length; i++) binary += String.fromCharCode(bytes[i]);
        return btoa(binary);
    }

    async function printViaLan(element, options = {}) {
        const { ip, port = 9100, widthDots = 576, baseUrl = '', csrfToken = '' } = options;
        if (!ip) throw new Error('Enter the printer\'s IP address first.');

        const payload = await buildPrintPayload(element, widthDots);
        const res = await fetch(baseUrl + '/api/thermal_print_relay.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                csrf_token: csrfToken,
                ip: ip,
                port: String(port),
                data: bytesToBase64(payload)
            })
        });
        const json = await res.json();
        if (!json.status) {
            throw new Error(json.message || 'LAN print failed.');
        }
    }

    // ==================== Preferences (per browser/device) ====================

    function getPreference() {
        try { return JSON.parse(localStorage.getItem(PREF_KEY) || 'null'); } catch (e) { return null; }
    }
    function setPreference(patch) {
        const current = getPreference() || {};
        localStorage.setItem(PREF_KEY, JSON.stringify(Object.assign({}, current, patch)));
    }

    return {
        isUsbSupported, printViaUsb, usbForgetPairedDevice, pairUsb,
        isBluetoothSupported, printViaBluetooth, bluetoothForgetPairedDevice, pairBluetooth,
        printViaLan,
        getPreference, setPreference,
    };
})();
