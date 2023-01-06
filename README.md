# zenstruck/changelog

[![CI](https://github.com/zenstruck/changelog/actions/workflows/ci.yml/badge.svg)](https://github.com/zenstruck/changelog/actions/workflows/ci.yml)

Generate pretty release changelogs using the commit log and Github API. Changelog entries are in the following
format:

```
{short hash} {summary} (#{PR number}) by {author}, {co-author 1}, {co-author n}...
```

Some notes about the format:
1. Currently, this format is hard-coded and can't be customized
2. Merge commits are excluded
3. PR number is only added if not already in the summary (and a PR exists for the commit)
4. Author/Co-Author's are converted to GitHub username links if possible to take advantage
   of [GitHub Release Avatar List](https://github.blog/changelog/2021-09-14-releases-now-have-an-avatar-list/)

See [an example](https://github.com/zenstruck/foundry/releases/tag/v1.13.3) of a release generated using this package.

## Installation

To avoid dependency conflicts with this tool, it is recommended to install as an executable [PHAR](#phar) or install in
your project (or globally) using the [composer bin plugin](#composer-bin-plugin).

### PHAR

```bash
wget https://github.com/zenstruck/changelog/releases/latest/download/changelog.phar -O changelog && chmod +x changelog
mv changelog ~/bin # assumes ~/bin is in your PATH
```

### Composer Bin Plugin

Requires the [bamarni/composer-bin-plugin](https://github.com/bamarni/composer-bin-plugin).

```bash
# locally in your project
composer bin changelog require zenstruck/changelog

# globally
composer global bin changelog require zenstruck/changelog
```

## Configuration

The `changelog` binary should be executable either globally or via `vendor/bin/changelog`. For the remainder of this
documentation, it is assumed this is available as `changelog`.

This tool requires a GitHub Personal Access Token to access the GitHub API. You can configure this in two ways:
1. `GITHUB_API_TOKEN` environment variable (ie prefix `changelog` commands with `GITHUB_API_TOKEN=your-token`)
2. Configure the token globally: `changelog configure` and follow the instructions to generate/save your token

## Usage

> **Note**: Commands that detect the repository from your current directory use the `upstream` origin
> if available.

### Changelog Preview

Generate a changelog preview in your console:

```bash
# "interactive", detects repository from current directory, from=last release on GitHub, to=default branch
changelog generate

# equivalent to above, "generate" is the "default command"
changelog

# outputs changelog for "your/repository" from tag "v1.0.0" to branch "main"
changelog generate --repository=your/repository --from=v1.0.0 --to=main
```

Run `changelog generate --help` to see full command documentation.

### Create Changelog File

Create a `CHANGELOG.md` file for the current repository based on existing releases. This
file is created in the current working directory. It is up to you to add/commit to the repository.

```bash
# create a CHANGELOG.md
bin/changelog file:create

# customize the filename
bin/changelog file:create --filename=changes.md

# exclude releases marked as "pre-release"
bin/changelog file:create --exclude-pre-releases
```

### Create Release

Create (and optionally push) a release changelog (exclude `--push` to preview what the release will look like):

```bash
# "interactive" - detects repository from current directory, suggests next version
changelog release

# generates changelog from v1.0.0 to main and creates v1.1.0 release on GitHub that has the changelog as the body
changelog release v1.1.0 --repository=your/repository --from=v1.0.0 --target=main --push

# detects repository from current directory, from=last release on GitHub, target=default branch
changelog release v1.1.0 --push
```

You can use semantic versioning keywords as the next version. The following example assumes your last release was
_v1.0.0_.

```bash
changelog release bug --push # creates v1.0.1 release
changelog release feature --push # creates v1.1.0 release
changelog release major --push # creates v2.0.0 release
```

Run `changelog release --help` to see full command documentation.

### Release Status Dashboard

Generate a simple dashboard for a GitHub organization showing package release statuses.

```bash
changelog dashboard my-org

# will ask for organization and give option to save as default.
# if saved, subsequent calls to the command will not require the organization argument
changelog dashboard
```
