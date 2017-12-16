import sys

def partition(file):
    lines = file.readlines();
    ret = [[], [], []]

    part = 0
    for line in lines:
        if part == 0:
            if line[:4] == 'use ':
                part += 1
        elif part == 1:
            if line[:4] != 'use ':
                part += 1
        ret[part].append(line)

    return ret


if __name__ == '__main__':
    filename = sys.argv[1]
    with open(filename) as f:
        parts = partition(f)

    if parts[1]:
        ordered = parts[1][:]
        ordered.sort()
        if cmp(parts[1], ordered) != 0:
            with open(filename, 'w') as f:
                f.write(''.join(parts[0] + ordered + parts[2]))

