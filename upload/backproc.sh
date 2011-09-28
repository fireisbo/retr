#!/bin/sh
# start the process.php file in the background
uphome="$HOME/www/upload"    
log=/tmp/runmen.log
process=/home1/fireisbo/www/retract/process.php
file="$1"
ifile=$uphome/input/$file
wfile=$uphome/work/$file
ofile=$uphome/output/$file
lfile=$uphome/work/lock

mv $ifile $wfile
echo $$ > $lfile 
date >>$log
echo "starting process.php with $wfile" >>$log
cat "$wfile"  >>$log 

if [ -f "$wfile" ] 
then
echo "this file ($wfile) exists" >>$log
fi

#echo php -f $process $wfile   
#php -f $process $wfile   
php -f $process $wfile  >$uphome/work/$file.out 2>&1
echo "process.php finished with $wfile" >>$log
date >>$log
mv $wfile $ofile
mv $wfile.out $ofile.out
rm -f  $uphome/work/lock
EM=`grep email $ofile | sed -e 's/#__email://'`
if [ -n "$EM" ] 
then
mail -s "job completed" $EM <$ofile.out
fi
exit 0
