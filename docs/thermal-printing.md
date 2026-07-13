
# Direct Thermal Receipt Printing (USB / Bluetooth / WiFi-LAN)

The USB / Bluetooth / WiFi-LAN buttons on the thermal receipt page
(`invoice_thermal.php`) talk straight to an ESC/POS receipt printer - no OS
print dialog, no PDF, no A4 page. The receipt is rendered to an image (via
html2canvas) and sent to the printer as a raw raster bit-image, so output is
pixel-identical to what's on screen across all three transports.

Implementation: `assets/js/thermal-printer.js` (shared rasterization/encoding
+ per-transport send logic) and `api/thermal_print_relay.php` (WiFi/LAN only).

## How to use it

1. Open any invoice's thermal receipt (`invoice_thermal.php?id=...`).
2. Pick the printer width preset matching your machine (58mm/80mm, or Custom
   if you know its exact dot width) - the on-screen preview resizes to match.
3. Click **USB**, **Bluetooth**, or **WiFi/LAN**:
   - USB/Bluetooth: the browser shows its own device picker - select your
     printer. After the first pairing, it's remembered on this browser/device.
   - WiFi/LAN: enter the printer's IP address (and port, default 9100) first.
4. Optionally check **"Always print automatically via..."** and pick a
   transport - from then on, opening any receipt on this browser/device sends
   it straight to that printer on load instead of opening the browser's print
   dialog. (The *first* USB/Bluetooth pairing always has to be a manual click
   - browsers don't allow the device picker to open on page load without one.)

## USB

- **Chrome or Edge only.** WebUSB is not supported in Safari or Firefox.
- The printer needs a **raw USB interface** the browser can claim. True for
  most generic ESC/POS thermal receipt printers ("80mm thermal receipt
  printer, USB + Bluetooth" on Amazon/AliExpress/IndiaMART).
- **If the printer doesn't show up in the picker, or connects but nothing
  prints:** its USB interface is likely already claimed by an OS-level print
  driver. Try uninstalling that driver, or check Device Manager (Windows) for
  whether it installed as a standard "Printer" (blocks WebUSB) vs. a generic
  USB device.

## Bluetooth

- **Chrome or Edge only**, with Bluetooth turned on.
- **Only BLE-based printers work at all.** Web Bluetooth cannot access
  Classic Bluetooth (SPP), which is what most budget thermal printers
  (including many "USB + Bluetooth" combo units) actually use for their
  Bluetooth mode - that's a browser/OS platform limitation with no workaround.
- The code targets one specific, very common BLE service
  (`49535343-fe7d-...`, an ISSC/Microchip "transparent UART" profile) used by
  a large share of generic Chinese thermal printer BLE modules. If a printer
  uses BLE but a different service, pairing will fail with a clear "this
  printer does not expose the expected Bluetooth print service" error -
  that's not necessarily Classic Bluetooth, it may just need its actual
  service/characteristic UUIDs added.
- Writes are chunked (180 bytes) with a small delay between each - most cheap
  BLE printer modules drop data if flooded faster than they can buffer it, so
  a full receipt takes a few seconds over Bluetooth (this is expected, not a bug).

## WiFi / LAN

- Browsers have **no raw TCP socket API**, so a LAN printer (raw port 9100,
  the near-universal "AppSocket"/JetDirect protocol almost all network
  printers speak) can't be reached directly from JS. `api/thermal_print_relay.php`
  relays the print data server-side instead.
- **This only works if the web server itself can reach the printer's
  network.** Fine for an on-premise/local install; a cloud-hosted deployment
  cannot print to a device sitting behind a shop's home/office router this way.
- Errors from unreachable IPs, refused connections, etc. are surfaced
  directly in the status line with the actual `fsockopen` error message.

## Troubleshooting a failed print

Check the status line under the buttons - errors show there, and full
details are in the browser console (`console.error`). Common cases:

- **"No printer was selected"** - the picker was closed without choosing a device.
- **"No writable USB endpoint found..."** - selected device doesn't expose a
  raw OUT endpoint the way expected; may need interface/endpoint numbers
  hardcoded for that specific printer model once known.
- **"This printer does not expose the expected Bluetooth print service..."**
  - either Classic Bluetooth (unfixable from a browser) or a BLE printer using
  different UUIDs (fixable - send the actual service/characteristic UUIDs).
- **Connects but prints garbage / nothing (any transport)** - try a different
  printer width preset; the hardware may use a different native dot-width
  than assumed.
- **WiFi/LAN "Could not reach IP:PORT"** - wrong IP/port, printer not on the
  same network as the server, or (for cloud-hosted installs) the server
  simply can't reach the shop's local network at all.
