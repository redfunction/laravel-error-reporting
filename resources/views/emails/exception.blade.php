<!DOCTYPE html>
<html>
    <body>
        <h2>Error</h2>
        <table>
            <tr>
                <th>Error class</th>
                <td><?= get_class($error) ?></td>
            </tr>
            <tr>
                <th>Message</th>
                <td><?= $error->getMessage() ?></td>
            </tr>
            <tr>
                <th>File and line</th>
                <td><?= $error->getFile() ?>(<?= $error->getLine() ?>)</td>
            </tr>
        </table>

        <h2>Request</h2>
        <table>
            <?php foreach ($request as $key => $value): ?>
            <tr>
                <th><?= $key ?></th>
                <td><?php if (array_key_exists('request', $encryptedData) && array_key_exists($key, $encryptedData['request'])) echo "<i>{$encryptedData['request'][$key]} (length " . strlen($value) . " characters)</i>"; else var_dump($value); ?></td>
            </tr>
            <?php endforeach; ?>
        </table>

        <h2>Server</h2>
        <table>
            <?php foreach ($server as $key => $value): ?>
            <tr>
                <th><?= $key ?></th>
                <td><?php if (array_key_exists('server', $encryptedData) && array_key_exists($key, $encryptedData['server'])) echo "<i>{$encryptedData['server'][$key]} (length " . strlen($value) . " characters)</i>"; else var_dump($value); ?></td>
            </tr>
            <?php endforeach; ?>
        </table>

        <h2>Backtrace</h2>
        <?php foreach ($error->getTrace() as $idx => $t): ?>
        <?php
        $line = $idx . ") ";
        if (!empty($t["class"])) {
            $line .= $t["class"];
        }
        if (!empty($t["type"])) {
            $line .= $t["type"];
        }
        if (!empty($t["function"])) {
            $line .= $t["function"];
        }
        if (!empty($t["args"])) {
            $vars = [];
            foreach ($t["args"] as $tidx => $v) {
                $vartype = gettype($v);
                if ($vartype == "string") {
                    $vartype .= " '" . $v . "'";
                } elseif ($vartype == "object") {
                    $vartype .= "(" . get_class($v) . ")";
                }
                $vars[] = $vartype;
            }
            $line .= "(" . join(",", $vars) . ")";
        }
        $line .= "<br /><i>";
        if (!empty($t["file"])) {
            $line .= $t["file"];
        }
        if (!empty($t["line"])) {
            $line .= "(" . $t["line"] . ")";
        }
        $line .= "</i>";
        ?>
        <?= $line ?><br/>

        <?php endforeach; ?>
    </body>
</html>