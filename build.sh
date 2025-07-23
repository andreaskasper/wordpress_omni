# This script builds a Docker image named 'wpbuild' using the current directory's Dockerfile,
# then runs a temporary container from that image to execute 'build.php' with PHP.
# The current working directory is mounted to '/app/' inside the container to provide access to project files.


docker build -t wpbuild .
docker run --rm \
    -v ${PWD}:/app/ \
    wpbuild php /app/build.php