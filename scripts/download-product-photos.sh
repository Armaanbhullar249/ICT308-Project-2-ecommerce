#!/bin/sh
# Download catalogue photos into uploads/products
DIR="$(cd "$(dirname "$0")/.." && pwd)/uploads/products"
mkdir -p "$DIR"
cd "$DIR"
UA="Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15"

fetch() {
  name="$1"
  url="$2"
  if [ -s "$name" ]; then
    return 0
  fi
  if curl -fL -A "$UA" -o "$name.tmp" "$url"; then
    mv "$name.tmp" "$name"
    echo "saved $name"
  else
    rm -f "$name.tmp"
    echo "SKIP $name"
  fi
}

# Laptops
fetch laptop-01.jpg "https://images.unsplash.com/photo-1496181133206-80ce9b88a853?auto=format&fit=crop&w=900&h=900&q=70"
fetch laptop-02.jpg "https://images.unsplash.com/photo-1517336714731-489689fd1ca8?auto=format&fit=crop&w=900&h=900&q=70"
fetch laptop-03.jpg "https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=900&h=900&q=70"
fetch laptop-04.jpg "https://images.unsplash.com/photo-1484788984921-03950022c9ef?auto=format&fit=crop&w=900&h=900&q=70"
fetch laptop-05.jpg "https://images.unsplash.com/photo-1511385348-a52b4a160dc2?auto=format&fit=crop&w=900&h=900&q=70"
fetch laptop-06.jpg "https://images.unsplash.com/photo-1588872657578-7efd1f1555ed?auto=format&fit=crop&w=900&h=900&q=70"
fetch laptop-07.jpg "https://images.unsplash.com/photo-1611078489935-0cb964de46d6?auto=format&fit=crop&w=900&h=900&q=70"
fetch laptop-08.jpg "https://images.unsplash.com/photo-1541807084-5c52b6b3adef?auto=format&fit=crop&w=900&h=900&q=70"
# Phones
fetch phone-01.jpg "https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?auto=format&fit=crop&w=900&h=900&q=70"
fetch phone-02.jpg "https://images.unsplash.com/photo-1510557880182-3d4d3cba35a5?auto=format&fit=crop&w=900&h=900&q=70"
fetch phone-03.jpg "https://images.unsplash.com/photo-1592899677977-9c10ca588bbd?auto=format&fit=crop&w=900&h=900&q=70"
fetch phone-04.jpg "https://images.unsplash.com/photo-1580910051074-3eb694886505?auto=format&fit=crop&w=900&h=900&q=70"
fetch phone-05.jpg "https://images.unsplash.com/photo-1512499617640-c74ae3a79d37?auto=format&fit=crop&w=900&h=900&q=70"
fetch phone-06.jpg "https://images.unsplash.com/photo-1601784551446-20c9e07cdbdb?auto=format&fit=crop&w=900&h=900&q=70"
fetch phone-07.jpg "https://images.unsplash.com/photo-1556656793-08538906a9f8?auto=format&fit=crop&w=900&h=900&q=70"
fetch phone-08.jpg "https://images.unsplash.com/photo-1598327105666-5b89351aff22?auto=format&fit=crop&w=900&h=900&q=70"
# Audio
fetch audio-01.jpg "https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=900&h=900&q=70"
fetch audio-02.jpg "https://images.unsplash.com/photo-1484704849700-f032a568e944?auto=format&fit=crop&w=900&h=900&q=70"
fetch audio-03.jpg "https://images.unsplash.com/photo-1590658268037-6bf12165a8df?auto=format&fit=crop&w=900&h=900&q=70"
fetch audio-04.jpg "https://images.unsplash.com/photo-1545127398-14699f92334b?auto=format&fit=crop&w=900&h=900&q=70"
fetch audio-05.jpg "https://images.unsplash.com/photo-1608043152269-423dbba4e7e1?auto=format&fit=crop&w=900&h=900&q=70"
fetch audio-06.jpg "https://images.unsplash.com/photo-1545454675-3531b543be5d?auto=format&fit=crop&w=900&h=900&q=70"
fetch audio-07.jpg "https://images.unsplash.com/photo-1487215078519-e21cc028cb29?auto=format&fit=crop&w=900&h=900&q=70"
fetch audio-08.jpg "https://images.unsplash.com/photo-1572536147248-ac59a8abfa4d?auto=format&fit=crop&w=900&h=900&q=70"
# Accessories / computing
fetch acc-01.jpg "https://images.unsplash.com/photo-1527864550417-7fd91fc51a46?auto=format&fit=crop&w=900&h=900&q=70"
fetch acc-02.jpg "https://images.unsplash.com/photo-1587829741301-dc798b83add3?auto=format&fit=crop&w=900&h=900&q=70"
fetch acc-03.jpg "https://images.unsplash.com/photo-1618384887929-16ec33cad4e9?auto=format&fit=crop&w=900&h=900&q=70"
fetch acc-04.jpg "https://images.unsplash.com/photo-1597872200969-2b65d56bd16b?auto=format&fit=crop&w=900&h=900&q=70"
fetch acc-05.jpg "https://images.unsplash.com/photo-1583863788434-e58a36330cf0?auto=format&fit=crop&w=900&h=900&q=70"
fetch acc-06.jpg "https://images.unsplash.com/photo-1625723044792-44de16ccb4e9?auto=format&fit=crop&w=900&h=900&q=70"
fetch acc-07.jpg "https://images.unsplash.com/photo-1591488320449-011701bb6701?auto=format&fit=crop&w=900&h=900&q=70"
fetch acc-08.jpg "https://images.unsplash.com/photo-1527443224154-c4a3942d3acf?auto=format&fit=crop&w=900&h=900&q=70"
# Tablets
fetch tablet-01.jpg "https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?auto=format&fit=crop&w=900&h=900&q=70"
fetch tablet-02.jpg "https://images.unsplash.com/photo-1561154464-82e9adf32764?auto=format&fit=crop&w=900&h=900&q=70"
fetch tablet-03.jpg "https://images.unsplash.com/photo-1589739900243-4b52cd9b104e?auto=format&fit=crop&w=900&h=900&q=70"
fetch tablet-04.jpg "https://images.unsplash.com/photo-1542751371-adc38448a05e?auto=format&fit=crop&w=900&h=900&q=70"
# Wearables
fetch watch-01.jpg "https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=900&h=900&q=70"
fetch watch-02.jpg "https://images.unsplash.com/photo-1434493789847-2f02dc6ca35d?auto=format&fit=crop&w=900&h=900&q=70"
fetch watch-03.jpg "https://images.unsplash.com/photo-1579586337278-3befd40fd17a?auto=format&fit=crop&w=900&h=900&q=70"
fetch watch-04.jpg "https://images.unsplash.com/photo-1508685096489-7aacd43bd3b1?auto=format&fit=crop&w=900&h=900&q=70"
# TVs
fetch tv-01.jpg "https://images.unsplash.com/photo-1593359677879-a4bb92f829d1?auto=format&fit=crop&w=900&h=900&q=70"
fetch tv-02.jpg "https://images.unsplash.com/photo-1461151304267-38535e780c79?auto=format&fit=crop&w=900&h=900&q=70"
fetch tv-03.jpg "https://images.unsplash.com/photo-1593784991095-a205069470cd?auto=format&fit=crop&w=900&h=900&q=70"
fetch tv-04.jpg "https://images.unsplash.com/photo-1593359677879-a4bb92f829d1?auto=format&fit=crop&w=1000&h=700&q=70"
# Cameras
fetch cam-01.jpg "https://images.unsplash.com/photo-1516035069371-29a1b244cc32?auto=format&fit=crop&w=900&h=900&q=70"
fetch cam-02.jpg "https://images.unsplash.com/photo-1502920917128-1aa500764cbd?auto=format&fit=crop&w=900&h=900&q=70"
fetch cam-03.jpg "https://images.unsplash.com/photo-1606983340126-99ab4feaa64a?auto=format&fit=crop&w=900&h=900&q=70"
fetch cam-04.jpg "https://images.unsplash.com/photo-1495706533460-0b11df16c1b8?auto=format&fit=crop&w=900&h=900&q=70"
# Gaming
fetch game-01.jpg "https://images.unsplash.com/photo-1606144042614-b2417e99c4e3?auto=format&fit=crop&w=900&h=900&q=70"
fetch game-02.jpg "https://images.unsplash.com/photo-1612287230202-1ff1cd067c00?auto=format&fit=crop&w=900&h=900&q=70"
fetch game-03.jpg "https://images.unsplash.com/photo-1593305841991-05c297ba4575?auto=format&fit=crop&w=900&h=900&q=70"
fetch game-04.jpg "https://images.unsplash.com/photo-1542751110-97427bbecf20?auto=format&fit=crop&w=900&h=900&q=70"
# Smart home
fetch home-01.jpg "https://images.unsplash.com/photo-1558002038-1055907df827?auto=format&fit=crop&w=900&h=900&q=70"
fetch home-02.jpg "https://images.unsplash.com/photo-1543512214-318c7553f230?auto=format&fit=crop&w=900&h=900&q=70"
fetch home-03.jpg "https://images.unsplash.com/photo-1558618666-fcd25c85cd64?auto=format&fit=crop&w=900&h=900&q=70"
fetch home-04.jpg "https://images.unsplash.com/photo-1558002038-1055907df827?auto=format&fit=crop&w=1000&h=800&q=70"

rm -f test.jpg
echo "done: $(ls -1 *.jpg | wc -l) photos"
