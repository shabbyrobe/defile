Defile - PHP Stream Wrapping File System Emulation Disaster
===========================================================

This is not ready for prime time yet.

TODO:

- chmod, chgrp, chown
- Generator streams (sort of like /dev/zero)
- Memory should be able to optionally use php://temp (maybe should use php://memory for
  everything)
- StreamRegistry::umask() instead of system umask() (though system umask() should still
  be usable)

