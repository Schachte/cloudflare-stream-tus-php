Q := @

.PHONY: run-dev-build
run-dev-build:
	$(Q)echo "Rebuilding image & running TUS server"
	docker-compose up --build

# Faster if you don't need to rebuild the image
.PHONY: run-dev
run-dev:
	$(Q)echo "Running TUS server"
	$(Q)docker-compose up