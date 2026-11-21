# Dokumentasi KosManager

Indeks dokumen aktif. Laporan historis (fase audit) ada di `docs/archive/`.

## Dokumen aktif

- `DOKUMENTASI.md` — dokumentasi utama aplikasi.
- `ERD.md` — entity relationship diagram.
- `RELEASE_WIDE_SECURITY_REPORT.md` — laporan keamanan rilis-wide dan TODO C2.
- `NASKAH_PRESENTASI.md` / `TEKS_NARASI_PRESENTASI.md` — naskah presentasi.
- `DOKUMENTASI.docx`, `NASKAH_PRESENTASI_KOSMANAGER.docx` — versi Word.

## Arsip

- `docs/archive/` — laporan fase (PHASE_4 … PHASE_8) dan `TECHNICAL_DEBT_MIGRATION_REPAIR.md`.
- Laporan audit lain yang sempat ada di root sudah dihapus pada commit `chore(repo): remove unused root artifacts…`.

## Catatan

- Jangan menambah laporan baru di root; taruh di `docs/` atau `docs/archive/`.
- File artefak (log, output test, script debug) tidak boleh di-commit; gunakan `.gitignore`.
