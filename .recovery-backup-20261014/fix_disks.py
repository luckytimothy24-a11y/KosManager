import pathlib
files = [
    'tests/Feature/PaymentSecurityTest.php',
    'tests/Feature/IsolationBoundaryTest.php',
    'tests/Feature/PembayaranTest.php',
    'tests/Feature/TransferBankPaymentTest.php',
    'tests/Feature/TenantPaySelectionUxTest.php',
    'tests/Feature/OwnerBankAccountTenantDisplayTest.php',
    'tests/Feature/Phase44TenantMarketplaceTest.php',
]
for f in files:
    p = pathlib.Path(f)
    t = p.read_text(encoding='utf-8')
    orig = t
    t = t.replace("Storage::fake('local')", "Storage::fake('private')")
    t = t.replace("Storage::disk('local')", "Storage::disk('private')")
    t = t.replace("Storage::put('bukti-pembayaran/", "Storage::disk('private')->put('bukti-pembayaran/")
    if t != orig:
        p.write_text(t, encoding='utf-8')
        print(f'fixed {f}')
    else:
        print(f'no change {f}')
