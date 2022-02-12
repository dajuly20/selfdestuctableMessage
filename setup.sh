#!/bin/bash
if [ "$EUID" -ne 0 ]; 
then echo "Please run as root!"
exit
fi

ln -s deltmpmsg.sh /usr/bin/deltmpmsg.sh
(sudo crontab -u www-data -l 2>/dev/null; echo "*/5 * * * * ./usr/bin/deltmpmsg.sh") | crontab -
if [ $?  -eq 0 ]; then
echo "JO"
else
echo Â"FUCK"
fi
