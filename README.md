PHP package versions
====================

This is a super-simple command-line tool. If you are wondering how many people
use each version of a Composer package, this will create a CSV file with the
average number of downloads per day, grouped by version (for example, 2.3.0 and 2.3.1
will be grouped into 2.3)

Usage
-----

```bash
# Replace symfony/symfony with the name of the package in Composer/Packagist.
php versions.php symfony/symfony
```

The average number of downloads per day, grouped by month, will be written to a CSV file.

You can then open the file in Excel or LibreOffice and create some nice charts.
