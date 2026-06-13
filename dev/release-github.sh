#!/usr/bin/env bash

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT_DIR"

BRANCH="$(git rev-parse --abbrev-ref HEAD)"

if [ "$BRANCH" = "HEAD" ]; then
	echo "Releases must be created from the trunk branch, not a detached HEAD." >&2
	exit 1
fi

if [ "$BRANCH" != "trunk" ]; then
	echo "Releases must be created from trunk. Current branch: $BRANCH" >&2
	exit 1
fi

if [ -n "$(git status --porcelain)" ]; then
	echo "Releases require a clean working tree." >&2
	exit 1
fi

if ! command -v gh >/dev/null 2>&1; then
	echo "GitHub CLI is required to create a release." >&2
	exit 1
fi

if ! gh auth status >/dev/null 2>&1; then
	echo "GitHub CLI must be authenticated before creating a release." >&2
	exit 1
fi

git fetch origin trunk --quiet

if [ "$(git rev-parse HEAD)" != "$(git rev-parse refs/remotes/origin/trunk)" ]; then
	echo "Releases must be created from the current origin/trunk HEAD." >&2
	exit 1
fi

VERSION="$(node -p "require('./package.json').version")"
TAG="$VERSION"
ZIP_FILE="release/redirection.zip"

if git ls-remote --exit-code --refs --tags origin "refs/tags/$TAG" >/dev/null 2>&1; then
	echo "Git tag $TAG already exists on origin." >&2
	exit 1
fi

if gh release view "$TAG" >/dev/null 2>&1; then
	echo "GitHub release $TAG already exists." >&2
	exit 1
fi

printf 'Create GitHub release for version %s from branch %s? [y/N] ' "$VERSION" "$BRANCH"
read -r CONFIRM

case "$CONFIRM" in
	[yY]|[yY][eE][sS])
		;;
	*)
		echo "Release cancelled."
		exit 0
		;;
esac

pnpm plugin:zip

if git show-ref --verify --quiet "refs/tags/$TAG"; then
	TAG_COMMIT="$(git rev-parse "refs/tags/$TAG^{}")"
	HEAD_COMMIT="$(git rev-parse HEAD)"

	if [ "$TAG_COMMIT" != "$HEAD_COMMIT" ]; then
		echo "Git tag $TAG already exists locally but does not point to HEAD." >&2
		exit 1
	fi

	echo "Git tag $TAG already exists locally and points to HEAD."
else
	git tag -a "$TAG" -m "Release $TAG"
fi

git push origin "$TAG"

gh release create "$TAG" "$ZIP_FILE" \
	--verify-tag \
	--title "$TAG" \
	--generate-notes
