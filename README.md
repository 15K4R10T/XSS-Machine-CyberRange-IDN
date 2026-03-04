# Lab XSS Injection — IDN-CyberRange

<div align="center">

Lingkungan praktik Cross-Site Scripting (XSS) berbasis Docker dengan enam modul bertingkat — dari teknik dasar hingga eksploitasi lanjutan.

[![Port](https://img.shields.io/badge/Port-8082-2496ED)](#cara-menjalankan)
[![Modules](https://img.shields.io/badge/Modules-6-22c55e)](#modul)
[![Flags](https://img.shields.io/badge/Flags-6-e63946)](#flags--challenges)
[![Stack](https://img.shields.io/badge/Stack-PHP%208.1%20%2B%20MySQL-777BB4)](#arsitektur)

</div>

---

## Daftar Isi

- [Tentang Lab](#tentang-lab)
- [Modul](#modul)
- [Arsitektur](#arsitektur)
- [Cara Menjalankan](#cara-menjalankan)
- [Struktur File](#struktur-file)
- [Database](#database)
- [Flags & Challenges](#flags--challenges)
- [Commands](#commands)
- [Disclaimer](#disclaimer)

---

## Tentang Lab

Lab ini menyediakan lingkungan praktik Cross-Site Scripting (XSS) yang terisolasi dan siap pakai dalam satu Docker container. Dirancang untuk pelatihan keamanan siber tingkat enterprise, dengan enam modul yang mencakup tiga tipe XSS utama beserta teknik lanjutan seperti filter bypass, CSP bypass, dan session hijacking.

Setiap modul dilengkapi dengan:
- Penjelasan vulnerability context dan kode yang rentan
- Objectives yang terstruktur dan terukur
- Visualisasi alur serangan secara real-time
- Sistem hint bertingkat untuk memandu proses eksploitasi

---

## Modul

### Basic Series

| # | Nama | Path | Tingkat | Deskripsi |
|---|------|------|---------|-----------|
| 1 | Reflected XSS | `/basic-1/` | Basic | Input dari parameter URL direfleksikan langsung ke halaman tanpa HTML encoding |
| 2 | Stored XSS | `/basic-2/` | Basic | Payload disimpan di database via form komentar dan dieksekusi setiap kali halaman dibuka |
| 3 | DOM-Based XSS | `/basic-3/` | Basic | Payload diproses oleh JavaScript di sisi klien menggunakan `innerHTML` dan `location.hash` |

### Advanced Series

| # | Nama | Path | Tingkat | Deskripsi |
|---|------|------|---------|-----------|
| 4 | XSS + Filter Bypass | `/advanced-1/` | Advanced | Melewati filter blacklist dengan 3 level kesulitan yang semakin ketat |
| 5 | XSS + CSP Bypass | `/advanced-2/` | Advanced | Mengeksploitasi misconfigured Content Security Policy (`unsafe-inline`) |
| 6 | XSS + Session Hijacking | `/advanced-3/` | Advanced | Mencuri session cookie via Stored XSS karena `HttpOnly` flag tidak diset |

---

## Arsitektur

```
Container: lab-xss  (port 8082)
├── Supervisor (process manager)
│   ├── Apache 2 + PHP 8.1  -->  port 8082
│   └── MySQL 8.0           -->  internal only
└── /var/www/html/
    ├── /basic-1/
    ├── /basic-2/
    ├── /basic-3/
    ├── /advanced-1/
    ├── /advanced-2/
    └── /advanced-3/
```

Lab ini dapat dijalankan bersamaan dengan lab lain karena menggunakan port yang berbeda:

| Lab | Container | Port |
|-----|-----------|------|
| SQL Injection | `lab-sqli` | `8080` |
| XML Injection | `lab-xml` | `8081` |
| XSS Injection | `lab-xss` | `8082` |

---

## Cara Menjalankan

### Prasyarat

Docker Engine terinstall di sistem host.

```bash
# Install Docker jika belum ada
curl -fsSL https://get.docker.com | sh
sudo usermod -aG docker $USER
newgrp docker
```

### Opsi A — Build dari Source Code

```bash
# Clone repository
git clone https://github.com/15K4R10T/IDN-CyberRange.git
cd IDN-CyberRange/lab-xss

# Build dan jalankan
chmod +x run.sh
./run.sh
```

### Opsi B — Load dari Docker Image
drive: tar file (https://drive.google.com/drive/folders/1gj7lIzy7J-LzXrHtfdARC09cb6focyxm?usp=sharing)

```bash
# Download lab-xss-image.tar.gz dari Releases, lalu:
docker load < lab-xss-image.tar.gz
docker run -d --name lab-xss -p 8082:80 --restart unless-stopped lab-xss
```

### Akses Lab

```
http://localhost:8082          # Jika dijalankan di mesin lokal
http://<IP-VM>:8082            # Jika dijalankan di VM atau server
```

---

## Struktur File

```
lab-xss/
├── Dockerfile
├── run.sh                     Build & deploy otomatis (port 8082)
├── entrypoint.sh              Inisialisasi MySQL + start supervisor
├── supervisord.conf           Process manager config
├── apache.conf                Virtual host Apache
├── init.sql                   Schema database + data dummy
└── web/
    ├── index.php              Dashboard
    ├── basic-1/
    │   └── index.php          Modul 1: Reflected XSS
    ├── basic-2/
    │   └── index.php          Modul 2: Stored XSS
    ├── basic-3/
    │   └── index.php          Modul 3: DOM-Based XSS
    ├── advanced-1/
    │   └── index.php          Modul 4: Filter Bypass (3 level)
    ├── advanced-2/
    │   └── index.php          Modul 5: CSP Bypass
    ├── advanced-3/
    │   └── index.php          Modul 6: Session Hijacking
    └── includes/
        ├── db.php             Database connection
        ├── shared_css.php     CSS theme bersama
        ├── nav.php            Navbar
        └── footer.php         Footer
```

---

## Database

Credentials koneksi (internal container):

```
Host     : 127.0.0.1
Database : labxss
User     : labuser
Password : labpass123
```

| Tabel | Digunakan oleh | Keterangan |
|-------|---------------|-----------|
| `comments` | Basic 2 | Komentar yang disimpan — target Stored XSS |
| `users` | Advanced 3 | Akun dengan `session_token` dan `secret` flag |
| `feedback` | Advanced 3 | Form feedback — target Stored XSS untuk session hijacking |
| `search_log` | Basic 1 | Log query pencarian |
| `guestbook` | Referensi | Data tambahan untuk simulasi |

---

## Flags & Challenges

| Flag | Modul | Cara Mendapatkan |
|------|-------|-----------------|
| `FLAG{reflected_xss_poc}` | Basic 1 | Eksekusi `alert(document.cookie)` via parameter URL |
| `FLAG{stored_xss_persistent}` | Basic 2 | Sisipkan payload yang persisten di database komentar |
| `FLAG{dom_xss_no_server}` | Basic 3 | Eksekusi XSS via `location.hash` tanpa server melihat payload |
| `FLAG{filter_bypass_success}` | Advanced 1 | Bypass semua 3 level filter blacklist |
| `FLAG{csp_misconfigured}` | Advanced 2 | Eksekusi script meskipun CSP aktif |
| `FLAG{xss_session_hijack_admin}` | Advanced 3 | Ambil alih akun admin via cookie theft |

---

## Commands

```bash
# Status container
docker ps

# Log real-time
docker logs -f lab-xss

# Stop lab
docker stop lab-xss

# Start ulang
docker start lab-xss

# Masuk ke shell container (debug)
docker exec -it lab-xss bash

# Reset dan rebuild dari awal
docker rm -f lab-xss && ./run.sh
```

---

## Disclaimer

> Lab ini dibuat **hanya untuk keperluan edukasi dan pelatihan keamanan siber** di lingkungan yang terisolasi.
> Jangan gunakan teknik yang dipelajari pada sistem, jaringan, atau aplikasi tanpa izin tertulis dari pemiliknya.
> ID-Networkers tidak bertanggung jawab atas segala bentuk penyalahgunaan materi dalam repositori ini.

---

<div align="center">
  <sub>Dibuat oleh <strong>ID-Networkers</strong> — Indonesian IT Expert Factory</sub>
</div>
