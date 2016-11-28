# Production settings

## Supervisord

As you may read in [quick tour](quick_tour.md) you have to run `formapro:message-queue:consume` in order to process messages 
The php process is not designed to work for a long time. So it has to quit periodically.
Or, the command may exit because of error or exception. 
Something has to bring it back and continue message consumption.
We advise you to use [Supervisord](http://supervisord.org/) for that. 
It starts processes and keep an eye on them while they are working. 


Here an example of supervisord configuration.
It runs four instances of `formapro:message-queue:consume` command.

```ini
[program:pf_message_consumer]
command=/path/to/app/console --env=prod --no-debug --time-limit="now + 5 minutes" formapro:message-queue:consume
process_name=%(program_name)s_%(process_num)02d
numprocs=4
autostart=true
autorestart=true
startsecs=0
user=apache
redirect_stderr=true
```

_**Note**: Pay attention to `--time-limit` it tells the command to exit after 5 minutes._

[back to index](../index.md)