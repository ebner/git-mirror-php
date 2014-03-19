# Git mirror

Author: Hannes Ebner, 2014

PHP script to mirror one Git repository to another; to be used as post-commit hook. Clones and fetches a Git repository to push it to another mirror-repository. Uses `clone --bare`, `fetch --prune` and `push --mirror` in different steps.

## Possible sources of failure:

  * The public key of www-data has to have read access to the source repository as well as write access to the target repository.
  * The connection will fail if the SSH server is not trusted, to avoid this the server should be access from the command line at least once to get its fingerprint into the local SSH configuration.

## License

Hannes Ebner licenses this work under the terms of the Apache License 2.0 (the "License"); you may not use this file except in compliance with the License. See the LICENSE file distributed with this work for the full License.
