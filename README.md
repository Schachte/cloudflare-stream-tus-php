# Cloudflare Stream TUS in PHP

Features an easily deployable PHP server and UI for handling direct creator uploads for Cloudflare Stream.

## Running

First, create a `.env` file with the following details at the root of the directory:

```
CLOUDFLARE_ACCOUNT_ID=your_account_id
CLOUDFLARE_API_TOKEN=your_api_token
```

- `make run-dev` to run the server
- `make run-dev-build` to run the server and force rebuild the image


## Access

- UI: `http://localhost:3000`
- Upload endpoint: `https://localhost:3000/upload`