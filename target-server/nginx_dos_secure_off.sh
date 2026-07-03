[root@kt10ap52 bin]# cat nginx_dos_secure_off.sh
#!/bin/bash
set -e

ln -sf /etc/nginx/conf.d/dos_enforce.off /etc/nginx/conf.d/dos_enforce.conf
nginx -t && systemctl reload nginx
echo "✅ DoS Protection DISABLED"

[root@kt10ap52 bin]#
