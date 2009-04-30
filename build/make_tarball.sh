#!/bin/bash

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
    echo `svn info $TRUNK | grep Revision:[[:space:]]*[[:digit:]]*$ | grep -o [[:digit:]]*$`
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
createTemporaryBuild
createReleaseFile
cleanUp
exit 0
