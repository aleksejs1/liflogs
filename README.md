# Google location history dump parser

## Get started

1. Copy ./config.php to ./config.local.php

```
cp ./config.php ./config.local.php
```

2. Configure your login and password in ./config.local.php

```
vi ./config.local.php
```

## Usage

1. Put your file from Semantic Location History to [project folder]/data/raw

2. Configure filename in ./config.local.php

3. Run public/index.php in your browser



Code will parse Semantic Location History file and save data to sqlite [project folder]/data/database/database.db file.

You will see on the page all places, which you has been visited sorted by visiting frequency.

You be abel to filter your places by date.
