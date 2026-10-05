#!/bin/sh
# Pasang / perbarui POS di VPS. Jalankan dari mana saja:
#   sh ~/pos-backend/docker/deploy.sh
#
# Caddy proyek bekuin (container bekuin-web-1) memegang port 80/443 dan
# meneruskan pos-begul.my.id ke POS lewat gateway jaringan bekuin:
#   reverse_proxy 172.18.0.1:8080
# Karena itu .env berisi POS_BIND=172.18.0.1 (hanya bisa dijangkau dari
# dalam VPS, tidak terbuka ke internet).
set -e
cd "$(dirname "$0")/.."

PROXY="${PROXY_CONTAINER:-bekuin-web-1}"
BIND=$(grep -E '^POS_BIND=' .env | cut -d= -f2)
PORT=$(grep -E '^POS_PORT=' .env | cut -d= -f2)
BIND="${BIND:-127.0.0.1}"
PORT="${PORT:-8080}"

echo "== Ambil kode terbaru"
git pull --ff-only

echo "== Build & jalankan container POS (port $BIND:$PORT)"
docker compose up -d --build --remove-orphans

echo "== Menunggu aplikasi siap"
i=0
until docker exec "$PROXY" wget -qO- "http://$BIND:$PORT/api/ping" >/dev/null 2>&1; do
    i=$((i + 1))
    if [ "$i" -ge 30 ]; then
        echo "GAGAL: $PROXY belum bisa menjangkau http://$BIND:$PORT. Cek: docker compose logs app --tail 30"
        exit 1
    fi
    sleep 2
done
echo "BERHASIL: Caddy ($PROXY) bisa menjangkau POS di http://$BIND:$PORT"
