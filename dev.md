#### vbox

    cd /var/www/home-www/yusam/github/yusam-hub/s3-sdk
    composer update
    sh phpunit

#### dockers

    docker exec -it yusam-php81 bash
    docker exec -it yusam-php81 sh -c "htop"

    docker exec -it yusam-php81 sh -c "cd /var/www/data/yusam/github/yusam-hub/s3-sdk && composer update"
    docker exec -it yusam-php81 sh -c "cd /var/www/data/yusam/github/yusam-hub/s3-sdk && sh phpunit"

#### curl