# toggl-jira

:clock10: Download time entries from Toggl and use them to log time in Jira

## Setup

### Python envionment

It is recommanded to use [virtualenv](https://virtualenv.pypa.io/en/stable/). This project uses **python3.5**

```bash
pip install -r requirements.txt
```

### Environment variables

```bash
cp .env.example .env.client
```

### Command execution

```bash
export $(cat .env.client | xargs)
python app.py 2015-09-29 2015-09-30
```

# License

MIT © [Matt Alexander](https://github.com/mattalexx)
