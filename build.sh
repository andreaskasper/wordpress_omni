docker build -t wpbuild .
docker run --rm \
    -v ${PWD}:/app/ \
    wpbuild php /app/build.php