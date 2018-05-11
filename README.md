# toggl-jira

[![Build Status](https://travis-ci.com/GabLeRoux/toggl-jira.svg?branch=master)](https://travis-ci.com/GabLeRoux/toggl-jira)

:clock10: Download time entries from Toggl and use them to log time in Jira

# ⚠ This is still a Work in Progress

## Setup

### Python envionment

It is recommanded to use [virtualenv](https://virtualenv.pypa.io/en/stable/). This project uses **python3.6**

```bash
pip install -r requirements.txt
```

### Environment variables

```bash
cp .env.example .env.project_name
```

### Command execution

```bash
export $(cat .env.project_name | xargs)
python app.py 2015-09-29 2015-09-30
```

# License

MIT © [Matt Alexander](https://github.com/mattalexx)
