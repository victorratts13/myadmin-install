<?php

/**
 * Cofigurações de servidor
 * e projeto de atualização
 */
$server = "https://updater.grupocnv.me";
$project = "checkout-myadmin";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $version = $_POST['version'] ?? null;
    $channel = $_POST['channel'] ?? null;

    if ($version && $channel) {
        // Sanitização simples
        // $version = escapeshellarg($version);
        // $channel = escapeshellarg($channel);

        // URL do arquivo .zip
        $zipUrl = "$server/checkout-myadmin/$channel/$version.zip";
        $zipFile = __DIR__ . "/$channel-$version.zip";

        // Baixar o arquivo .zip usando wget
        $command = "wget -O $zipFile $zipUrl";
        exec($command, $output, $returnVar);

        if ($returnVar === 0) {
            // Descompactar o arquivo .zip
            $unzipCommand = "unzip -o $zipFile -d " . escapeshellarg(__DIR__);
            exec($unzipCommand, $unzipOutput, $unzipReturnVar);

            if ($unzipReturnVar === 0) {
                // Remover arquivos de instalação
                unlink($zipFile);
                unlink(__FILE__);
                unlink('index.html');
                if(is_dir(__DIR__ . '/vendor')){
                    exec('php artisan key:generate');
                }
                // Retornar status 200
                echo json_encode(['status' => 'success']);
            } else {
                // Erro ao descompactar
                echo json_encode(['status' => 'error', 'message' => 'Erro ao descompactar o arquivo.']);
            }
        } else {
            // Erro ao baixar
            echo json_encode(['status' => 'error', 'message' => 'Erro ao baixar o arquivo.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Parâmetros ausentes.']);
    }
} else {
    echo file_get_contents("{$server}/{$project}/main.json");
}
