<?php

namespace Deployer;

task('media:push', function () {
    if (null === input()->getArgument('stage')) {
        throw new \RuntimeException("The target instance is required for media:push command. [Error code: 1488150029848]");
    }
    $config = array_merge_recursive(get('media_default'), get('media'));

    $src = get('deploy_path') . '/current';
    if (!trim($src)) {
        throw new \RuntimeException('You need to specify a source path.');
    }

    $dst = get('media_rsync_dest');
    while (is_callable($dst)) {
        $dst = $dst();
    }
    if (!trim($dst)) {
        throw new \RuntimeException('You need to specify a destination path.');
    }

    $server = \Deployer\Task\Context::get()->getServer()->getConfiguration();
    $host = $server->getHost();
    $port = $server->getPort() ? ' -p' . $server->getPort() : '';
    $identityFile = $server->getPrivateKey() ? ' -i ' . $server->getPrivateKey() : '';
    $user = !$server->getUser() ? '' : $server->getUser() . '@';

    $flags = isset($config['flags']) ? '-' . $config['flags'] : false;
    runLocally("rsync {$flags} -e 'ssh$port$identityFile' {{media_rsync_options}}{{media_rsync_includes}}{{media_rsync_excludes}}{{media_rsync_filter}} '$dst/' '$user$host:$src/' ", 0);
})->desc('Synchronize media between instances.');
