#!/bin/bash

checkWorkingCopyHasValidRevisionNumber()
{
    cat <<EOF

Working copy revision numbers are not updated after a commit. Hence, 
in order to attach a valid revision number to the release, you need 
to do an svn update before running this script. 

See: "Mixed Revision Working Copies"
http://svnbook.red-bean.com/en/1.4/svn.basic.in-action.html

If you need to perform an svn update enter "x" to exit this script.
Or hit any other key to continue...

EOF

    read REPLY
    if [ "$REPLY" = 'x' ]
    then
        exit 0	
    fi
}

# absolute path to parent of "build/"
# $1 - this script's $0 arg
getTrunk()
{
    cd `dirname $1`/
    cd ..
    echo `pwd -P`
}

createTemporaryBuild()
{
    local FILE
    local FILES=(
        README \
        LICENCE \
        phemto.php \
        lifecycle.php \
        repository.php \
        LICENCE \
        VERSION        
        )

    mkdir -p ${TMP_BUILD_DIR}'phemto'

    for FILE in ${FILES[@]}
    do
        cp ${TRUNK}${FILE} ${TMP_BUILD_DIR}'phemto/'
    done

    makeDocs
    echo "revision: `getCurrentRevision $TRUNK`" >> ${TMP_BUILD_DIR}'phemto/VERSION'
}

makeDocs()
{
    mkdir -p ${TMP_BUILD_DIR}'phemto/docs'
    cp ${TRUNK}'docs/bundled.css' ${TMP_BUILD_DIR}'phemto/docs/'
    xsltproc ${TRUNK}'docs/xslt/bundled.xslt' ${TRUNK}'docs/xml/index.xml' > ${TMP_BUILD_DIR}'phemto/index.html'
}

getCurrentRevision()
{
    svn up $TRUNK 
    echo `svn info $TRUNK | grep Revision:[[:space:]]*[[:digit:]]*$ | grep -o [[:digit:]]*$`
    return 0
}

createReleaseFile()
{
    cd $TMP_BUILD_DIR
    tar -zcf $NAME *
    mv $NAME $WRITABLE_DIR
    echo "release file is: "${WRITABLE_DIR}${NAME} 
}

cleanUp()
{
    rm -rf $TMP_BUILD_DIR
}

#-----------------------------------------------------

WRITABLE_DIR=/tmp/
TMP_BUILD_DIR=${WRITABLE_DIR}'phemto-tmp-build/'
TRUNK=`getTrunk $0`/
NAME=phemto_`cat ${TRUNK}'VERSION'`.tar.gz

checkWorkingCopyHasValidRevisionNumber
createTemporaryBuild
createReleaseFile
cleanUp
exit 0
