#!/bin/sh
# Pasang / perbarui POS di VPS lalu sambungkan ke jaringan Caddy yang
# memegang port 80/443 (proyek bekuin). Jalankan dari mana saja:
#   sh ~/pos-backend/docker/deploy.sh
#
# Sambungan jaringan dibuat manual (docker network connect), bukan lewat
# docker compose, karena di Docker 29 sambungan dari compose kadang
# terpasang tanpa IP. Sambungan manual tetap ada saat restart/reboot,
# hanya perlu dibuat ulang setelah container dibuat ulang (skrip ini).
set -e
cd "$(dirname "$0")/.."

NET="${PROXY_NETWORK:-bekuin_default}"
PROXY="${PROXY_CONTAINER:-bekuin-web-1}"
APP=pos-app-1

echo "== Ambil kode terbaru"
git pull --ff-only

echo "== Build & jalankan container POS"
docker compose up -d --build --remove-orphans

echo "== Sambungkan $APP ke jaringan $NET (nama: pos-app)"
docker network disconnect -f "$NET" "$APP" >/dev/null 2>&1 || true
docker network connect --alias pos-app "$NET" "$APP"

echo "== Menunggu aplikasi siap"
i=0
until docker exec "$PROXY" wget -qO- http://pos-app/api/ping >/dev/null 2>&1; do
    i=$((i + 1))
    if [ "$i" -ge 30 ]; then
        echo "GAGAL: $PROXY belum bisa menjangkau pos-app. Cek: docker compose logs app --tail 30"
        exit 1
    fi
    sleep 2
done

IP=$(docker inspect "$APP" --format "{{(index .NetworkSettings.Networks \"$NET\").IPAddress}}")
echo "BERHASIL: Caddy ($PROXY) tersambung ke POS di $IP"
