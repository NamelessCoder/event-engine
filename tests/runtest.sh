#!/usr/bin/env bash
EXPECTED=`cat tests/expected.txt`
PHP=`which php`
OUT=`"$PHP" example/app.php`
DIFF=`diff -y -W 166 --suppress-common-lines <(echo -e "$EXPECTED") <(echo -e "$OUT")`
DIFFLINES=`echo "$DIFF" | wc -l`

if [ $DIFFLINES -gt 3 ];
then
    echo ""
    echo "$DIFFLINES lines were different in the example application's output, a maximum of 3 (all containing time/date stamps) was expected. The difference is shown below:"
    echo "-------------------------------------------------------------------------------------------------------------------------------------------------------------------------"
    echo "$DIFF"
    echo "-------------------------------------------------------------------------------------------------------------------------------------------------------------------------"
    echo ""
    exit 1;
else
    echo "Test successful"
fi;
