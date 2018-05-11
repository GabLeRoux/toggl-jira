from click.testing import CliRunner

import get_all_time_entries_between


def test_get_all_time_entries_between_cli():
    runner = CliRunner()
    result = runner.invoke(get_all_time_entries_between.cli, ['foo', 'bar'])
    assert result.exit_code == -1
    assert isinstance(result.exception, ValueError)
    assert str(result.exception) == 'Incorrect data format, should be YYYY-MM-DD'
