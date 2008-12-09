cd ../..
NAME=phemto_`cat phemto/VERSION`.tar.gz
xsltproc phemto/docs/xslt/bundled.xslt phemto/docs/xml/index.xml > phemto/index.html
FILES=(phemto/README \
          phemto/LICENCE \
          phemto/index.html \
          phemto/docs/bundled.css \
          phemto/phemto.php \
          phemto/lifecycle.php \
          phemto/repository.php \
          phemto/LICENCE \
          phemto/VERSION)
tar -zcf $NAME ${FILES[*]}
