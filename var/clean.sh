cd files/
find -name "*.xml" -mtime +6 | xargs rm
find -name "*.png" -mtime +6 | xargs rm
find -name "*.jpeg" -mtime +6 | xargs rm
find -name "*.jpg" -mtime +6 | xargs rm
find -name "*.txt" -mtime +6 | xargs rm
find . -type d -empty -exec rmdir {} \;
