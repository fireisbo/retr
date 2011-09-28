#!/bin/sh
# run the process.php file in the foreground
# but it might take breaks, and run for several hours.
SCRIPT=`readlink -f $0`
# Absolute path this script is in, thus /home/user/bin
SCRIPTPATH=`dirname $SCRIPT`
uphome="$SCRIPTPATH"
log=/tmp/runmen.log
process=$SCRIPTPATH/../process.php
echo $process
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
