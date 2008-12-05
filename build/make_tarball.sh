cd ../..
NAME=phemto_`cat phemto/VERSION`.tar.gz
FILES=(phemto/README \
          phemto/LICENCE \
          phemto/phemto.php \
          phemto/lifecycle.php \
          phemto/repository.php \
          phemto/LICENCE \
          phemto/VERSION)
tar -zcf $NAME ${FILES[*]}
