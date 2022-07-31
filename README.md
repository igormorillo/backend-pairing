# Comando de control de consumos

## Necesidades del comando

En Holaluz estamos preocupados por el fraude en las lecturas de electricidad y hemos decidido implementar un detector de lecturas sospechosas.

Algunos clientes nos han telefoneado sospechando que algunos okupas han intervenido sus líneas eléctricas y es por ello que puedes encontrar algunas lecturas extremadamente altas en comparación con su uso regular.

Al mismo tiempo, sospechamos que algunos clientes están interviniendo las líneas eléctricas de su edificio y también puedes encontrar lecturas extremadamente bajas.

Como todos sabemos, muchos sistemas en España están un poco anticuados y obtienen algunas lecturas en XML y otras en CSV, por lo que tenemos que ser capaces de implementar adaptadores para ambas entradas.

Para esta primera iteración, intentaremos identificar las lecturas que sean superiores o inferiores a la mediana anual ± 50%.

Por favor, escribe una aplicación de línea de comandos que tome un nombre de archivo como argumento (como 2016-readings.xml o 2016-readings.csv) y produzca una tabla con las lecturas sospechosas:

|   Client   | Month   | Suspicious | Median   |
|:----------:|---------|:----------:|----------|
| -clientid- | -month- | -reading-  | -median- |

* ** 

## Requisitos 

* php 7.4
* symfony 4.4
* Módulos de PHP (xml, git)

## Ejecución de Docker

Primero ejecutamos el comando build

```docker-compose build```

Luego lo podemos ejecutar desde la consola o desde docker desktop. La opción -d permite ejecutarlo como un demonio

```docker-compose up -d```

## Ejecución del comando

Para entrar al contenedor primero miramos en el listado de dockers el id del que queremos

```docker container ls```

Que devolverá una tabla de este estilo

| CONTAINER ID | IMAGE | COMMAND | CREATED       | STATUS       | PORTS                                     | NAMES |
| :-----: | ----- | :-----: |-----|-----|-----------------------| :-----: |
| 297747ec8a24 | backend-pairing_backend-pairing-apache | 283 | X minutes ago | Up X minutes | 0.0.0.0:80->80/tcp, 0.0.0.0:443->443/tcp  | php-apache|

Luego entramos en modo consola usando /bin/bash indicando el nombre del contenedor (también se puede hacer a través de ID)

```docker container exec -it php-apache /bin/bash```

Una vez dentro ejecutamos el comando de composer para instalar los requerimientos del proyecto.

```composer install```

Posteriormente, ya podremos ejecutar el comando solicitado. 

```bin/console app:control-consumos ficheros/2016-readings.xml```

NOTA: Recordar que el nombre del fichero incluirá la ruta relativas desde la raiz del proyecto. 

Por ejemplo:

```php bin/console app:control-consumos ficheros/2016-readings.xml```