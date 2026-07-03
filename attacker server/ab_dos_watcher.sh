[root@kt10ap51 bin]# cat ab_dos_watcher.sh
#!/bin/bash
LOCK="/var/run/ab_dos_watcher.lock"
exec 9>"$LOCK" || exit 1
flock -n 9 || exit 0

CONTROL="/var/run/ab/ab_dos_control"
STATE="/var/run/ab/ab_dos.state"
PIDFILE="/var/run/ab_dos.pid"
LOG="/var/log/ab_dos.log"

while true; do
    if [ -f "$CONTROL" ]; then
        ACTION=$(cat "$CONTROL" | tr -d '\r\n')
        echo "$(date) watcher sees ACTION='$ACTION'" >> "$LOG"

        case "$ACTION" in
            on)
                echo "$(date) starting ab_dos" >> "$LOG"
                /usr/local/bin/ab_dos_start.sh
                rm -f "$CONTROL"
                ;;
            off)
                echo "$(date) stopping ab_dos" >> "$LOG"
                /usr/local/bin/ab_dos_stop.sh
                rm -f "$CONTROL"
                ;;
            *)
                echo "$(date) unknown action: '$ACTION'" >> "$LOG"
                rm -f "$CONTROL"
                ;;
        esac
    fi
    sleep 2
done

[root@kt10ap51 bin]#
