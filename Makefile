NAME=780624616421.dkr.ecr.us-east-1.amazonaws.com/centralizador-latam
VERSION=0.0.1

auth:

	aws ecr get-login-password --region us-east-1 | docker login --username AWS --password-stdin 780624616421.dkr.ecr.us-east-1.amazonaws.com
build:
	docker build -t $(NAME):$(VERSION) . 
	docker system prune
push:
	docker push $(NAME):$(VERSION)
