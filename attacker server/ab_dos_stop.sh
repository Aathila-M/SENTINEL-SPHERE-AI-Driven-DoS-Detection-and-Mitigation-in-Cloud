[root@kt10ap51 bin]# cat ab_dos_stop.sh
#!/bin/bash
set -e

STATE="/var/run/ab_dos.state"
PIDFILE="/var/run/ab_dos.pid"
LOG="/var/log/ab_dos.log"

echo "$(date) stopping ab_dos" >> "$LOG"

if [ -f "$PIDFILE" ]; then
    kill "$(cat "$PIDFILE")" 2>/dev/null || true
    rm -f "$PIDFILE"
fi

echo "off" > "$STATE"
