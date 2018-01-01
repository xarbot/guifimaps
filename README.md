# Guifimaps

Aplicatiu de visualitzacio dels mapes de Guifi. L'aplicatiu es composa de dues parts

- Parsers. Hi ha un parser per cadascuna de les fonts d'informacio
    - Aplicatiu de monitoritzacio de mesh
    - Informacio recopilada de l'agent de libremap
    - Informacio recopilada del CNML de determinades zones
    - Informacio recopilada via snmp de les IP's de les antenes
- Visualitzacio
    - Es mostra tota la informacio recopilada en un mapa. Es fa servir la llibreria leaflet.

Per fer-lo anar cal crear una base de dades i crear la estructura de taules amb l'arxiu tables.sql
Editar l'arxiu `functions.php` amb les dades referents a usuari i contrasenya de la BD
Crear una carpeta dins de `/var/log/` anomenada parsers i una entrada al cron que s'executi cada hora i que apunti a l'arxiu `parsers/nova_captura.sh`

# Demo

http://sants.guifi.net/maps/

## JSON export

El mapa en qüestió posa a disposició l'accés de les dades via JSON

Objecte JSON que conté el timestamp de totes les captures: http://sants.guifi.net/maps/export/timestamps.php

Retorna per la última captura http://sants.guifi.net/maps/export/captura.php

Amb paràmetre timestamp per paràmetre retorna la captura anterior especificada: http://sants.guifi.net/maps/export/captura.php?timestamp=1514782506
