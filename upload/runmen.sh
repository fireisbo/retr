#!/bin/sh
# supervise executing the backproc.sh script.
# that runs in the background.
# Set uphome to the home working directory of 
# the cron scripts.
SCRIPT=`readlink -f $0`
# Absolute path this script is in, thus /home/user/bin
SCRIPTPATH=`dirname $SCRIPT`
uphome="$SCRIPTPATH"    
log=/tmp/runmen.log
cd $uphome
lock="$uphome/work/lock"    
date >>$log
# check to see if someone is already working.
if [ -f "$lock" ]
then
  echo "work directory is locked.">>$log
  cat $lock 
  exit 0
fi
# pick the oldest file in the input directory
#
ifile=`ls -rt -1 $uphome/input | head -1` 
if [ -n "$ifile" ]
then 
  echo "input file is $ifile">>$log
  ./backproc.sh "$ifile"  &
  #./backproc.sh "$ifile" >>$log
  echo "started backproc.sh with $ifile">>$log
fi
date >>$log
exit 0
