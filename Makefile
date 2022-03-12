run:
	docker run --rm -it \
	-v $(PWD):/application \
	-v /mnt/storage/web/wanscraper:/application/storage/app \
	wanscraper php wanscraper wanshow:update --env=prod

install:
	docker run --rm -it -v $(PWD):/application \
	wanscraper composer install --no-dev

install-dev:
	docker run --rm -it -v $(PWD):/application \
	wanscraper composer install
