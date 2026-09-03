<?php

namespace App\Services\DatabaseBackup;

use RuntimeException;

/**
 * Exception khusus untuk kegagalan operasi backup/restore database.
 * Dipakai untuk memastikan backup tidak pernah dianggap sukses
 * apabila proses gagal.
 */
class DatabaseBackupException extends RuntimeException {}
