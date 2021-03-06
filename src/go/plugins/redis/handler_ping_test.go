/*
** Zabbix
** Copyright (C) 2001-2019 Zabbix SIA
**
** This program is free software; you can redistribute it and/or modify
** it under the terms of the GNU General Public License as published by
** the Free Software Foundation; either version 2 of the License, or
** (at your option) any later version.
**
** This program is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
** GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License
** along with this program; if not, write to the Free Software
** Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
**/

package redis

import (
	"fmt"
	"github.com/mediocregopher/radix/v3"
	"reflect"
	"testing"
	"zabbix.com/pkg/plugin"
)

func TestPlugin_pingHandler(t *testing.T) {
	impl.Configure(&plugin.GlobalOptions{}, nil)

	stubConn := radix.Stub("", "", func(args []string) interface{} {
		return "PONG"
	})
	defer stubConn.Close()

	conn := &redisConn{
		client: stubConn,
	}

	brokenStubConn := radix.Stub("", "", func(args []string) interface{} {
		return ""
	})
	defer brokenStubConn.Close()

	brokenConn := &redisConn{
		client: brokenStubConn,
	}

	closedStubConn := radix.Stub("", "", func(args []string) interface{} {
		return ""
	})
	closedStubConn.Close()

	closedConn := &redisConn{
		client: closedStubConn,
	}

	type args struct {
		conn   redisClient
		params []string
	}
	tests := []struct {
		name    string
		p       *Plugin
		args    args
		want    interface{}
		wantErr bool
	}{
		{
			fmt.Sprintf("pingHandler should return %d if connection is ok", pingOk),
			&impl,
			args{conn: conn},
			pingOk,
			false,
		},
		{
			fmt.Sprintf("pingHandler should return %d if PING answers wrong", pingFailed),
			&impl,
			args{conn: brokenConn},
			pingFailed,
			false,
		},
		{
			fmt.Sprintf("pingHandler should return %d if connection failed", pingFailed),
			&impl,
			args{conn: closedConn},
			pingFailed,
			false,
		},
	}
	for _, tt := range tests {
		t.Run(tt.name, func(t *testing.T) {
			got, err := tt.p.pingHandler(tt.args.conn, tt.args.params)
			if (err != nil) != tt.wantErr {
				t.Errorf("Plugin.pingHandler() error = %v, wantErr %v", err, tt.wantErr)
				return
			}
			if !reflect.DeepEqual(got, tt.want) {
				t.Errorf("Plugin.pingHandler() = %v, want %v", got, tt.want)
			}
		})
	}
}
