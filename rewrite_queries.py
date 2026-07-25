import os
import re

def main():
    dirs = ['api', 'application']
    
    # We want to match: ->query("SELECT ...", [...]) or similar
    # It's easier to just do simple replacements on common tables.
    
    tenant_tables = [
        'activity_logs', 'brands', 'categories', 'challan_items', 
        'challans', 'company_settings', 'coupons', 'customer_payments', 'customers', 
        'expense_categories', 'expenses', 'held_bills', 'invoice_items', 'invoice_payments', 
        'invoices', 'login_logs', 'loyalty_transactions', 'notifications', 'payments', 
        'product_batches', 'product_images', 'products', 'purchase_items', 'purchase_return_items', 
        'purchase_returns', 'purchases', 'quotation_items', 'quotations', 'report_logs', 
        'role_permissions', 'roles', 'sales_return_items', 'sales_returns', 'stock_transactions', 
        'supplier_payments', 'suppliers', 'unit_conversions', 'units', 'users', 'printers'
    ]

    print("Tenant tables:", len(tenant_tables))

