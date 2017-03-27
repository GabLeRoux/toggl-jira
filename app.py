import datetime

import click
import os
from pprint import pprint

from toggl_client import TogglClientApi


class TogglJira(object):
    settings = {
        'token': os.environ.get('TOGGL_KEY'),
        'user_agent': 'toggl-jira'
    }
    toggle_client = TogglClientApi(settings)
    project_name = os.environ.get('WORKSPACE_NAME')

    if project_name is not None:
        workspace = toggle_client.get_workspace_by_name(project_name)

    def get_workspaces(self):
        return self.toggle_client.get_workspaces()

    def __repr__(self):
        return '<TogglJira %r, %s>' % (self.toggle_client.api_base_url, self.workspace)


pass_toggl_jira = click.make_pass_decorator(TogglJira)


def validate_date(date_text):
    try:
        datetime.datetime.strptime(date_text, '%Y-%m-%d')
    except ValueError:
        raise ValueError("Incorrect data format, should be YYYY-MM-DD")


@click.command()
@click.argument('date_from')
@click.argument('date_to')
@click.version_option('1.0')
@click.pass_context
def cli(ctx, date_from, date_to):
    """

    :type toggl_jira: TogglJira
    """
    print('from: %s' % date_from)
    print('to: %s' % date_to)

    ctx.obj = TogglJira()
    pprint(ctx.obj.get_workspaces())


if __name__ == '__main__':
    cli()
