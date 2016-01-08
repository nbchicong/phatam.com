#!/bin/bash
if [ $# -lt 1 ]
then
        exit 1;
fi
LOG=/tmp/merge.log
file_prefix=${2}

file_merge=${1}

echo "`date` Merge to file $file_merge ";
/usr/bin/mp3wrap -v $file_merge $file_prefix;

mv -f ${1}_MP3WRAP.mp3 $file_merge

/usr/bin/mp3val $file_merge -f -nb

if [ -f $file_merge -a $? -eq 0 ]
then
        exit 0;
fi
exit 1;

