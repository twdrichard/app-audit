#!/bin/bash
# @file site_backup.sh
# @author Richard@TowerWebDesign.co.uk
# Audit a website, convert the report to html and email the report

VALIDPARAMS=0

if [ "$#" -eq 2  ]; then		# archive a folder and scp it
	SSH_CONNECTION=$1
	EMAIL_TO=$2
	VALIDPARAMS=1
fi

if [ "$VALIDPARAMS" -eq 0 ]; then
	echo "$# params found"
	echo "Usage: $0 ssh_connection_name email_to"
	exit
fi

echo "Auditing $SSH_CONNECTION and emailing to $EMAIL_TO"

TEMPFILE=$(mktemp --suffix ".html")
TEMPFILE_WITH_HEADERS=$(mktemp --suffix ".html")

./application audit $SSH_CONNECTION | ansi2html > $TEMPFILE
printf "Subject: $SSH_CONNECTION Audit Report\nMime-Version: 1.0\nContent-Type: text/html\n\n" | cat - $TEMPFILE > $TEMPFILE_WITH_HEADERS
cat  $TEMPFILE_WITH_HEADERS  | ssmtp $EMAIL_TO

rm $TEMPFILE
rm $TEMPFILE_WITH_HEADERS

