# Run this file to set up a crontab for session management

crontab -l > cronfile

# The command executes '/var/www/html/includes/clearsessions.php' every hour.
# Replace '/your/path/to/file/' to wherever the file is.
echo "0 * * * * /usr/bin/php /your/path/to/file/clearsessions.php >/dev/null 2>&1" >> cronfile

# Install the new file we made
crontab cronfile
rm cronfile