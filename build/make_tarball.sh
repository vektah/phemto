#!/bin/bash

# Discover the absolute path to the main phemto dir in the 
# working copy (the repository dir: "phemto/trunk").
# $1 - the $0 arg of this script
getTrunk()
{
    cd `dirname $1`/
    cd ..
    echo `pwd -P`
}

# Releases are now tagged with the revision number (helps with 
# support queries). Note that working copy revision numbers do 
# not update following a commit* - hence this currency check.
# (*More info: "Mixed Revision Working Copies" 
# http://svnbook.red-bean.com/en/1.4/svn.basic.in-action.html)
checkWorkingCopyHasValidRevisionNumber()
{
    if [ "`isUpdated $TRUNK`" = 'false' ]
    then
        echo 'Please perform an svn update then run this script again.'
        exit 0
    fi
}

isUpdated()
{
    if [ "`svnversion $1 | grep ^[[:digit:]]*$`" ]
    then
        echo true
    else
        echo false
    fi
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
    echo "revision: `svnversion $TRUNK`" >> ${TMP_BUILD_DIR}'phemto/VERSION'
}

makeDocs()
{
    mkdir -p ${TMP_BUILD_DIR}'phemto/docs'
    cp ${TRUNK}'docs/bundled.css' ${TMP_BUILD_DIR}'phemto/docs/'
    xsltproc ${TRUNK}'docs/xslt/bundled.xslt' ${TRUNK}'docs/xml/index.xml' > ${TMP_BUILD_DIR}'phemto/index.html'
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
