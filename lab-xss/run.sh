#!/bin/bash
set -e

GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
CYAN='\033[0;36m'
NC='\033[0m'

IMAGE_NAME="lab-xss"
CONTAINER_NAME="lab-xss"
PORT="8082"

echo -e "${CYAN}"
echo "╔══════════════════════════════════════╗"
echo "║      Lab XSS Injection — IDN Lab     ║"
echo "╚══════════════════════════════════════╝"
echo -e "${NC}"

if ! command -v docker &> /dev/null; then
    echo -e "${RED}[x] Docker tidak ditemukan. Install dulu:${NC}"
    echo "    curl -fsSL https://get.docker.com | sh"
    echo "    sudo usermod -aG docker \$USER && exit"
    exit 1
fi

if docker ps -a --format '{{.Names}}' | grep -q "^${CONTAINER_NAME}$"; then
    echo -e "${YELLOW}[*] Menghapus container lama...${NC}"
    docker stop $CONTAINER_NAME 2>/dev/null || true
    docker rm $CONTAINER_NAME 2>/dev/null || true
fi

echo -e "${YELLOW}[*] Building Docker image...${NC}"
docker build -t $IMAGE_NAME .

echo -e "${YELLOW}[*] Menjalankan container di port ${PORT}...${NC}"
docker run -d \
    --name $CONTAINER_NAME \
    -p ${PORT}:80 \
    --restart unless-stopped \
    $IMAGE_NAME

echo -e "${YELLOW}[*] Menunggu service siap...${NC}"
for i in $(seq 1 20); do
    if curl -s -o /dev/null -w "%{http_code}" http://localhost:$PORT 2>/dev/null | grep -q "200"; then
        break
    fi
    printf "   Starting... (%d/20)\r" $i
    sleep 2
done

echo ""
VM_IP=$(hostname -I | awk '{print $1}')

echo -e "${GREEN}"
echo "╔══════════════════════════════════════════════════════╗"
echo "║   Lab XSS Injection berhasil dijalankan!             ║"
echo "╠══════════════════════════════════════════════════════╣"
echo "║   Buka di browser:                                   ║"
printf "║   http://%-44s║\n" "${VM_IP}:${PORT}"
echo "║                                                      ║"
echo "║   Basic:                                             ║"
echo "║   /basic-1/  -> Reflected XSS          (Basic 1)    ║"
echo "║   /basic-2/  -> Stored XSS             (Basic 2)    ║"
echo "║   /basic-3/  -> DOM-Based XSS          (Basic 3)    ║"
echo "║   Advanced:                                          ║"
echo "║   /advanced-1/ -> XSS + Filter Bypass  (Adv 1)      ║"
echo "║   /advanced-2/ -> XSS + CSP Bypass     (Adv 2)      ║"
echo "║   /advanced-3/ -> XSS + Session Hijack (Adv 3)      ║"
echo "╠══════════════════════════════════════════════════════╣"
echo "║   docker logs -f lab-xss    (lihat log)              ║"
echo "║   docker stop lab-xss       (stop)                   ║"
echo "║   docker start lab-xss      (start ulang)            ║"
echo "╚══════════════════════════════════════════════════════╝"
echo -e "${NC}"
