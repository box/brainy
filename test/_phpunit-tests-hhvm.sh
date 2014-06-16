#!/bin/sh
if which hhvm >/dev/null; then
    cat `which phpunit` | grep '/usr/bin/env php' | sed 's/ php / hhvm --php /' | sed 's/\$\*/\-\-verbose \-\-colors _runAllTests.php/' | /usr/bin/env sh
else
    echo "HHVM does not appear to be installed."
fi
