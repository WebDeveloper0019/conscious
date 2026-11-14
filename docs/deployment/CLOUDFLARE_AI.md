# Puji: Cloudflare activation on Hostinger

Puji keeps the existing PHP chat endpoint and request format. The frontend uses the original illustrated assistant design and avatar from the project history, with the display name **Puji** and subtitle **Your CI Assistant**.

## Provider setup

1. Create or sign in to a Cloudflare account at https://dash.cloudflare.com/.
2. Open **Workers AI**, then **Use REST API**.
3. Select **Create a Workers AI API Token**, review its account scope, and create it yourself. Save the token privately. The official template provides the required permissions; custom tokens need Workers AI Read and Edit.
4. Copy the Cloudflare **Account ID** from that page.
5. On Hostinger, edit `/home/u990067962/domains/drowdigital.in/private/ci-backend/backend/.env`. Update the existing AI settings, avoiding duplicate keys:

```dotenv
AI_PROVIDER=cloudflare
CF_ACCOUNT_ID=YOUR_CLOUDFLARE_ACCOUNT_ID
CF_AI_TOKEN=YOUR_PRIVATE_WORKERS_AI_TOKEN
CF_AI_MODEL=@cf/meta/llama-3.3-70b-instruct-fp8-fast
```

Keep the token out of chat, Git, JavaScript, screenshots, and the public document root. This REST integration does not require moving domain DNS, deploying a Worker, or exposing the local Ollama server. Use Cloudflare's account limits and usage dashboard to manage consumption; do not assume unlimited free inference.

## Minimal adapter update

The old `@cf/meta/llama-3.1-8b-instruct` model was retired. `CF_AI_MODEL` now selects the model through private configuration, defaulting to the available Llama 3.3 fast model. The system prompt, message validation, 20-messages/hour/IP limiter, response format, and local Ollama path are preserved. The same adapter also serves vendor writing assistance.

Deploy the updated private `backend/config.php` and `backend/lib/ai-client.php` before using the new setting. Public UI files are `assets/js/ai-assistant.js`, `assets/css/ai-assistant.css`, and `assets/images/assistant/website-guide.png`; the public loader in `assets/js/bharatcraft.js` must use the new asset version. Build production files with `php scripts/build-production.php --hostinger-fixed-root`, then run `php scripts/verify-production-release.php`. Do not upload the repository's local-path templates directly.

## Verify after configuration

From Hostinger SSH, this small inference request prints only a reply or failure, never the token:

```sh
php -r 'require "/home/u990067962/domains/drowdigital.in/private/ci-backend/backend/config.php"; require "/home/u990067962/domains/drowdigital.in/private/ci-backend/backend/lib/ai-client.php"; $reply=ai_complete("You are Puji, a concise shopping assistant.","Say hello in one short sentence.",64); if($reply===null){fwrite(STDERR,"AI_TEST_FAILED\n");exit(1);} echo "AI_TEST_OK\n",$reply,PHP_EOL;'
```

Then test the live widget: open/close, welcome chips, send a short question, receive a reply, and inspect its mobile layout. A working provider connection does not establish that every answer is correct. If the model call fails, check token/account/model access privately in Cloudflare; do not disable authentication, TLS, or the site's rate limiter.

The current chat endpoint sends only the system prompt and the customer's current message. It does **not** query live products, prices, stock, events, customer records, or conversation history. Cloudflare activation preserves that behavior; database grounding is a separate implementation task.

## References checked on 14 November 2026

- [Cloudflare Workers AI REST setup](https://developers.cloudflare.com/workers-ai/get-started/rest-api/)
- [Model retirement notice](https://developers.cloudflare.com/changelog/post/2026-05-08-planned-model-deprecations/)
- [Llama 3.3 fast model and API contract](https://developers.cloudflare.com/workers-ai/models/llama-3.3-70b-instruct-fp8-fast/)
