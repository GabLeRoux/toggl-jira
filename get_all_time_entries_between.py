import datetime
import json
import os
from pprint import pprint

import click

from toggl_client import TogglClientApi


class TogglJira(object):
    settings = {
        'token': os.environ.get('TOGGL_KEY'),
        'user_agent': 'toggl-jira'
    }
    toggle_client = TogglClientApi(settings)

    def get_all_entries_between(self, start_date, end_date):
        entries = []
        for workspace in self.toggle_client.get_workspaces():
            # Decode UTF-8 bytes to Unicode, and convert single quotes
            # to double quotes to make it valid JSON
            decoded_data = workspace.decode('utf8')
            # Load the JSON to a Python list & dump it back out as formatted JSON
            data = json.loads(decoded_data)
            s = json.dumps(data, indent=4, sort_keys=True)
            print(s)
            entries.__add__(self.toggle_client.get_project_times(workspace.id, start_date, end_date))
        return


pass_toggl_jira = click.make_pass_decorator(TogglJira)


def validate_date(date_text):
    try:
        datetime.datetime.strptime(date_text, '%Y-%m-%d')
    except ValueError:
        raise ValueError("Incorrect data format, should be YYYY-MM-DD")


@click.command()
@click.argument('start_date')
@click.argument('end_date')
@click.version_option('1.0')
@click.pass_context
def cli(ctx, start_date, end_date):
    print('from: %s' % start_date)
    print('to: %s' % end_date)

    ctx.obj = TogglJira()
    pprint(ctx.obj.get_all_entries_between(start_date, end_date))


if __name__ == '__main__':
    cli()
