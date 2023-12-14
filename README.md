# Google location history dump parser

## Usage

1. Put your file from Semantic Location History to [project folder]/data/raw

2. Configure filename in config.php

3. Run public/index.php in your browser



Code will parse Semantic Location History file and save data to sqlite [project folder]/data/database/database.db file.

You will see on the page all places, which you has been visited sorted by visiting frequency.

! DO NOT RUN THIS ON PRODUCTION. There is no security at all.
