# TitaKita

> The decentralised event-discovery layer — built for AI.

TitaKita is a self-hostable event & ticketing platform (a "node"). Run your own
instance, own your data, and stay discoverable: every node exposes a
machine-readable events feed, a `.well-known` descriptor and schema.org
structured data, so a future directory and AI agents can find and surface your
events.

## Quick start (Docker)

```bash
cd docker/all-in-one
./setup.sh            # creates .env + auto-generates secrets
docker compose up -d
```

Then open <http://localhost:8123/auth/register>. With the defaults you get a
zero-external-accounts "try it out" node — email is written to the logs and
payments are disabled, so you can explore without signing up for anything.

- 📋 Before going live: [`部署装前需知.md`](部署装前需知.md) (deployment pre-flight checklist)
- 🚀 Full deploy guide: [`docker/all-in-one/README.md`](docker/all-in-one/README.md)

## Tech stack

- **Backend** — Laravel (PHP), Domain-Driven Design
- **Frontend** — React + Vite (SSR)
- **Data** — PostgreSQL + Redis

## Directory contract

So a directory / AI layer can discover a node, every instance exposes:

| Surface | Path |
|---|---|
| Node descriptor | `/.well-known/titakita.json` |
| Public events feed (schema.org) | `/api/public/events` |
| AI guide | `/llms.txt` |
| Sitemap | `/sitemap.xml` |
| Event pages | schema.org `Event` JSON-LD embedded |
| Change webhooks (opt-in) | POST to `TITAKITA_DIRECTORY_URL` |

Nodes run fully standalone; the directory is purely opt-in
(`TITAKITA_OPT_IN_DIRECTORY`).

## License

Built on the open-source [Hi.Events](https://hi.events) project and distributed
under the **GNU AGPL-3.0** license. See [`LICENCE`](LICENCE).
