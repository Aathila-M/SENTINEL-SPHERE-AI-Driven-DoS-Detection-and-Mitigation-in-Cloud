[root@kt10ap52 bin]# cat nginx_dos_watcher.sh
#!/bin/bash
# NGINX DoS Protection Watcher
# Monitors /var/run/nginx_dos.state and applies the proper config

set -e

STATE_FILE="/var/run/nginx_dos.state"
LAST_STATE=""
LOGFILE="/var/log/nginx/ngx_dos_watcher.log"
ON_SCRIPT="/usr/local/bin/nginx_dos_secure_on.sh"
OFF_SCRIPT="/usr/local/bin/nginx_dos_secure_off.sh"

echo "$(date) - Watcher started" >> "$LOGFILE"

while true; do
    # Read current state from state file
    STATE=$(cat "$STATE_FILE" 2>/dev/null || echo "off")

    if [ "$STATE" != "$LAST_STATE" ]; then
        echo "$(date) - Detected state change: $STATE" >> "$LOGFILE"
        if [ "$STATE" = "on" ]; then
            echo "$(date) - Running ENABLE script" >> "$LOGFILE"
            bash "$ON_SCRIPT" >> "$LOGFILE" 2>&1
        else
            echo "$(date) - Running DISABLE script" >> "$LOGFILE"
            bash "$OFF_SCRIPT" >> "$LOGFILE" 2>&1
        fi
        LAST_STATE="$STATE"
    fi

    sleep 2
done

[root@kt10ap52 bin]#
