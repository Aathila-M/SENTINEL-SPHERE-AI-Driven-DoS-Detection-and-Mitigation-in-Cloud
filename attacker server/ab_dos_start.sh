[root@kt10ap51 bin]# cat ab_dos_start.sh
#!/bin/bash
set -e

### CONFIG ###
TARGET_IP="YOUR_TARGET_SERVER_IP" # Replace YOUR_TARGET_SERVER_IP with the IP address of your target server.
PORT="8080"
URL="http://${TARGET_IP}:${PORT}/"

TOTAL_REQUESTS=2000
CONCURRENCY=500

STATE="/var/run/ab_dos.state"
PIDFILE="/var/run/ab_dos.pid"
LOG="/var/log/ab_dos.log"
AB_BIN="/usr/bin/ab"
##############

# Prevent double-start
if [ -f "$PIDFILE" ]; then
    exit 0
fi

echo "on" > "$STATE"
echo "==============================================" >> "$LOG"
echo "$(date) DoS TEST STARTED" >> "$LOG"
echo " Target      : ${URL}" >> "$LOG"
echo " Requests    : ${TOTAL_REQUESTS}" >> "$LOG"
echo " Concurrency : ${CONCURRENCY}" >> "$LOG"
echo "==============================================" >> "$LOG"

# Run ApacheBench in background
(
    "$AB_BIN" -n "$TOTAL_REQUESTS" -c "$CONCURRENCY" "$URL" >> "$LOG" 2>&1
    echo "$(date) DoS TEST FINISHED" >> "$LOG"
    echo "off" > "$STATE"
    rm -f "$PIDFILE"
) &

# Save PID
echo $! > "$PIDFILE"
