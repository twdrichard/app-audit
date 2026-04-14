#!/bin/bash
# @file site_backup.sh
# @author Richard@TowerWebDesign.co.uk
# Audit a website, convert the report to html and email the report
# Uses ansi2html https://github.com/pixelb/scripts/blob/master/scripts/ansi2html.sh
# and ssmtp to send the email

VALIDPARAMS=0

if [ "$#" -eq 3  ]; then		# archive a folder and scp it
	SITE_NAME=$1
	SSH_CONNECTION=$2
	EMAIL_TO=$3
	VALIDPARAMS=1
fi

if [ "$VALIDPARAMS" -eq 0 ]; then
	echo "$# params found"
	echo "Usage: $0 site_name ssh_connection_name email_to"
	exit
fi

echo "Auditing $SITE_NAME and emailing to $EMAIL_TO"

TEMPFILE=$(mktemp --suffix ".html")
TEMPFILE_WITH_HEADERS=$(mktemp --suffix ".html")

./application audit $SSH_CONNECTION | ansi2html > $TEMPFILE
printf "Subject: $SITE_NAME Audit Report\nMime-Version: 1.0\nContent-Type: text/html\n\n" | cat - $TEMPFILE > $TEMPFILE_WITH_HEADERS
cat  $TEMPFILE_WITH_HEADERS  | ssmtp $EMAIL_TO

rm $TEMPFILE
rm $TEMPFILE_WITH_HEADERS

