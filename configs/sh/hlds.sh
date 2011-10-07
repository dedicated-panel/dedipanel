#!/bin/bash

SCREEN_NAME="$$SCREEN_NAME"

case "$1" in
    start)
        echo "starting "
        $$SCREEN
        exit ${?}
    ;;

    stop)
        if [ `$0 status` = "started." ]; then
            echo "stop"
            PID=`ps aux | grep SCREEN | grep $SCREEN_NAME | awk '{print $2}'`
            kill $PID
        fi

        exit ${?}
    ;;

    restart)
        $0 stop
        sleep 1
        $0 start

        exit ${?}
    ;;

    status)
        PID=`ps aux | grep SCREEN | grep $SCREEN_NAME | wc -l`
        
        if [ $PID -eq 1 ]; then
                echo "started."
        else
                echo "stopped."
        fi
    ;;
    
    *)
        echo "Usage: $0 {start|stop|restart|status}"
        exit ${?}
    ;;
esac
