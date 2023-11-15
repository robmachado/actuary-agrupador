# Agrupador

A classe Group tem como objetivo coletar dados contidos nos xml de envio do EFD-Reinf e converte-los para a estrutura anterior assincrona, com a finalidade de ser importado em sistemas que não são mantidos atualizados pelos seus criadores.


## Modo de funcionamento

Devem ser obtidos os xml de envio atuais com o limite de 50 eventos e passa-los em um array para o método da classe

### Critérios e Condições

1. os xml devem conter o mesmo tipo de evento
2. devem referenciar o mesmo contribuinte
3. a estrutura irá basear a montagem e as regras nos dados contidos no primeiro xml passado como parâmetro 
4. caso sejam passados mais de 50 eventos haverá um Exception


```php
error_reporting(E_ALL);
ini_set('display_errors', 'On');

require_once 'Group.php';
try {
    $xml = file_get_contents('Como_esta_saindo.xml');
    $out = Group::addXmlEvents([$xml]);


    header('Content-Type: application/xml');
    echo $out;
} catch (\Exception $e) {
    echo $e->getMessage();
}
```
