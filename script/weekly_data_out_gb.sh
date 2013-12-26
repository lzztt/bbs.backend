#!/bin/bash
days=$(ls -lt /home/web/www.houstonbbs.com/logs/access*.gz | wc -l)
first=$(basename $(ls -lt /home/web/www.houstonbbs.com/logs/access*.gz | tail -n 1 | awk '{print $9}') | cut -c 12-19)
last=$(basename $(ls -ltr /home/web/www.houstonbbs.com/logs/access*.gz | tail -n 1 | awk '{print $9}') | cut -c 12-19)

echo "$days days ($first - $last)"
echo -n "data out: "
zcat /home/web/www.houstonbbs.com/logs/access*.gz | awk '{sum+=$10} END {print sum/1024/1024/1024, "GB"}'
