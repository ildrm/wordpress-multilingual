# Installation

Production: upload `multilingual-core-0.1.0.zip` through Plugins → Add New, or extract its `multilingual-core` directory into `wp-content/plugins`. Activate per site or network-wide. Activation installs the shared schema, seeds each site’s current locale, assigns capabilities, and schedules a single rewrite flush.

Development: clone the repository and run `composer install`. Runtime does not require Composer because the package contains a constrained fallback PSR-4 loader.

Requirements are WordPress 6.9+, PHP 8.1+, MySQL/MariaDB versions supported by that WordPress release, and pretty permalinks for directory language URLs.

