[root@kt10ap52 bin]# cat nginx_dos_secure_on.sh
#!/bin/bash
set -e

ln -sf /etc/nginx/conf.d/dos_enforce.on /etc/nginx/conf.d/dos_enforce.conf
nginx -t && systemctl reload nginx
echo "✅ DoS Protection ENABLED"

[root@kt10ap52 bin]#
